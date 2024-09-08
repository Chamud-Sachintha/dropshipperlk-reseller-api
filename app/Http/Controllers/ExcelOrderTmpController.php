<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\city_list;
use App\Models\ExcelOrderError;
use App\Models\ExcelOrderItemsTmp;
use App\Models\ExcelOrderTmp;
use App\Models\InCourierDetail;
use App\Models\Order;
use App\Models\OrderEn;
use App\Models\Product;
use App\Models\Reseller;
use App\Models\ResellProduct;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExcelOrderTmpController extends Controller
{
    private $AppHelper;
    private $OrderTmp;
    private $OrderItemsTmp;
    private $ResellProduct;
    private $Reseller;
    private $OrderItems;
    private $Order;
    private $Product;
    private $CityList;
    private $InCourierInfo;
    private $OrderErr;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->OrderTmp = new ExcelOrderTmp();
        $this->OrderItemsTmp = new ExcelOrderItemsTmp();
        $this->ResellProduct = new ResellProduct();
        $this->Reseller = new Reseller();
        $this->OrderItems = new Order();
        $this->Order = new OrderEn();
        $this->Product = new Product();
        $this->CityList = new city_list();
        $this->InCourierInfo = new InCourierDetail();
        $this->OrderErr = new ExcelOrderError();
    }

    public function clearAllTempOrderTables(Request $request) {
        try {
            $this->clearTables();

            return $this->AppHelper->responseMessageHandle(1, "Operation Successfully");
        } catch (\Exception $e) {
            return $this->AppHelper->responseMessageHandle(0, "Error Occured " . $e->getMessage());
        }
    }

    public function commitTempOrdersTable(Request $request) {
        $tempOrderList = $this->OrderTmp->find_all();

        try {
            DB::beginTransaction();

            foreach ($tempOrderList as $eachOrder) {
                $orderItemsList = $this->OrderItemsTmp->find_by_order($eachOrder['order']);

                $this->OrderItems->save_order_item($orderItemsList->toArray());
                $this->Order->save_order($eachOrder->toArray());
            }

            DB::commit();
            $this->clearTables();
            return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->AppHelper->responseMessageHandle(0, "Error Occured " . $e->getMessage());
        }
    }

    public function getAllErrLogs(Request $request) {
        return $this->AppHelper->responseEntityHandle(1, "Operation sucesssfully", $this->OrderErr->find_all());
    }

    public function getTempOrderList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Token");
        } else {
            $temp_order_list = $this->OrderTmp->find_all();

            $dataList = array();
            foreach ($temp_order_list as $key => $value) {
                $product_info = $this->Product->find_by_id($value['product_id']);
                $resell_info = $this->ResellProduct->find_by_pid_and_sid($value['reseller_id'], $value['product_id']);
                $incourierDetails = $this->InCourierInfo->find_by_order($value['order']);

                $dataList[$key]['orderNumber'] = $value['order'];
                $dataList[$key]['totalAmount'] = $value['total_amount'];
                $dataList[$key]['courierName'] = $value['courier_name'];

                if ($incourierDetails != null) {
                    $dataList[$key]['TrackingNumber'] = $incourierDetails['way_bill'];
                    $dataList[$key]['courierName'] = "CeylonEx";
                } else if ($value['tracking_number'] != null) {
                    $dataList[$key]['TrackingNumber'] = $value['tracking_number'];
                } else {
                    $dataList[$key]['courierName'] = "-";
                    $dataList[$key]['TrackingNumber'] = "-";
                }

                if ($value['payment_status'] == 0) {
                    $dataList[$key]['paymentStatus'] = "Pending";
                } else if ($value['payment_status'] == 1) {
                    $dataList[$key]['paymentStatus'] = "Paid";
                } else {
                    $dataList[$key]['paymentStatus'] = "Refund";
                }

                if ($value['order_status'] == 0) {
                    $dataList[$key]['orderStatus'] = "Pending";
                } else if ($value['order_status'] == 1) {
                    $dataList[$key]['orderStatus'] = "Hold";
                } else if ($value['order_status'] == 2) {
                    $dataList[$key]['orderStatus'] = "Packaging";
                } else if ($value['order_status'] == 3) {
                    $dataList[$key]['orderStatus'] = "Cancle";
                } else if ($value['order_status'] == 4) {
                    $dataList[$key]['orderStatus'] = "In Courier";
                } else if ($value['order_status'] == 5) {
                    $dataList[$key]['orderStatus'] = "Delivered";
                } else if ($value['order_status'] == 6) {
                    $dataList[$key]['orderStatus'] = "Returned";
                } else if ($value['order_status'] == 7) {
                    $dataList[$key]['orderStatus'] = "Completed";
                } else if ($value['order_status'] == 8) {
                    $dataList[$key]['orderStatus'] = "Settled";
                } else if ($value['order_status'] == 9) {
                    $dataList[$key]['orderStatus'] = "Return Recieved";
                } else if ($value['order_status'] == 10) {
                    $dataList[$key]['orderStatus'] = "Rider Assigned";
                } else if ($value['order_status'] == 11) {
                    $dataList[$key]['orderStatus'] = "Resheduled";
                } else {
                    $dataList[$key]['orderStatus'] = "Complted";
                }

                $dateTime = new DateTime($value['created_at']);
                $formattedDate = $dateTime->format('Y-m-d');

                $dataList[$key]['remark'] = $value['remark'];
                $dataList[$key]['holdNotice'] = $value['hold_notice'];
                $dataList[$key]['orderPlaceDate'] = $formattedDate;
            }

            return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
        }
    }

    public function uploadOrdersFromExcelToTmpTable(Request $request)
    {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Token");
        } else {
            $reseller = $this->Reseller->find_by_token($request_token);

            $file = $request->file('file');
            $filePath = $file->getPathName();

            if (($handle = fopen($filePath, 'r')) !== false) {
                $data = [];

                while (($row = fgetcsv($handle, 1000, ",")) !== false) {
                    $data[] = $row;
                }

                fclose($handle);

                if ($this->createTempOrderList($data, $reseller)) {
                    return $this->AppHelper->responseMessageHandle(1, "Suceess Importing");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured While Importing");
                }
            } else {
                return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
            }
        }
        
    }

    // private function createTempOrderList($orderData, $reseller) {
    //     try {
    //         DB::beginTransaction();

    //         $runningRowNumber = null;

    //         DB::table((new ExcelOrderError())->getTable())->truncate();

    //         for ($eachRow = 1; $eachRow < count($orderData); $eachRow++) {
    //             $orderDataInfo = array();
    //             $runningRowNumber = $eachRow + 1;

    //             $resell_product = $this->ResellProduct->find_by_pid_and_sid($reseller->id, $orderData[$eachRow][0]);
    //             $orderId = $this->generateOrderNumber(10);

    //             $product_info = $this->Product->find_by_id($orderData[$eachRow][0]);

    //             if ($product_info == null || empty($product_info)) {
    //                 throw new \Exception("Invalid Product ID");
    //             }

    //             if (!$this->validateCity($orderData[$eachRow][3])) {
    //                 throw new \Exception("Invalid City");
    //             }
    
    //             $orderDataInfo['productId'] = $orderData[$eachRow][0];
    //             $orderDataInfo['resellerId'] = $reseller->id;
    //             $orderDataInfo['order'] =  $orderId;
    //             $orderDataInfo['name'] = $orderData[$eachRow][1];
    //             $orderDataInfo['address'] = $orderData[$eachRow][2];
    //             $orderDataInfo['city'] = $orderData[$eachRow][3];
    //             $orderDataInfo['district'] = $orderData[$eachRow][4];
    //             $orderDataInfo['contact_1'] = $orderData[$eachRow][5];
    //             $orderDataInfo['contact_2'] = $orderData[$eachRow][6];
    //             $orderDataInfo['quantity'] = $orderData[$eachRow][7];
    //             $orderDataInfo['totalAmount'] = $resell_product['price'] * $orderData[$eachRow][7];
    //             $orderDataInfo['paymentMethod'] = $orderData[$eachRow][8];
    //             $orderDataInfo['bankSlip'] = null;
    //             $orderDataInfo['isResellerCompleted'] = 0;
    //             $orderDataInfo['createTime'] = $this->AppHelper->get_date_and_time();
    
    //             $orderItemsTmpLog = $this->OrderItemsTmp->add_log($orderDataInfo);

    //             if ($orderItemsTmpLog) {
    //                 $orderTmpInfo = array();
    //                 $order_number =  $orderId;

    //                 $orderTmpInfo['resellerId'] = $reseller->id;
    //                 $orderTmpInfo['order'] = $orderId;
    //                 $orderTmpInfo['totalAmount'] = $resell_product['price'] * $orderData[$eachRow][7];;
    //                 $orderTmpInfo['paymentMethod'] = $orderData[$eachRow][8];
    //                 $orderTmpInfo['isResellerCompleted'] = 0;
    //                 $orderTmpInfo['createTime'] = $this->AppHelper->get_date_and_time();
    //                 $orderTmpInfo['remarkInfo'] = null;
    //                 $orderDataInfo['bankSlip'] = null;

    //                 $this->OrderTmp->add_log($orderDataInfo);
    //             }
    //         }

    //         DB::commit();
    //         return true;
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         $erorInfo = array();
    //         $erorInfo['rowNumber'] = $runningRowNumber;
    //         $erorInfo['errorMsg'] = $e->getMessage();
    //         $erorInfo['createTime'] = $this->AppHelper->day_time();

    //         $this->OrderErr->add_log($erorInfo);

    //         return false;
    //     }
        
    // }

    private function createTempOrderList($orderData, $reseller) {
        $runningRowNumber = null;
    
        // Clear any previous errors
        DB::table((new ExcelOrderError())->getTable())->truncate();
    
        // Loop through the order data
        for ($eachRow = 1; $eachRow < count($orderData); $eachRow++) {
            $orderDataInfo = [];
            $runningRowNumber = $eachRow + 1; // Adjust the row number for tracking
    
            try {
                // Find the reseller product and validate
                $resell_product = $this->ResellProduct->find_by_pid_and_sid($reseller->id, $orderData[$eachRow][0]);
                $orderId = $this->generateOrderNumber(10);
                $product_info = $this->Product->find_by_id($orderData[$eachRow][0]);
    
                // Check if the product exists
                if (is_null($product_info)) {
                    throw new \Exception("Invalid Product ID");
                }
    
                // Validate the city
                if (!$this->validateCity($orderData[$eachRow][3])) {
                    throw new \Exception("Invalid City");
                }
    
                // Prepare order data info
                $orderDataInfo = [
                    'productId' => $orderData[$eachRow][0],
                    'resellerId' => $reseller->id,
                    'order' => $orderId,
                    'name' => $orderData[$eachRow][1],
                    'address' => $orderData[$eachRow][2],
                    'city' => $orderData[$eachRow][3],
                    'district' => $orderData[$eachRow][4],
                    'contact_1' => $orderData[$eachRow][5],
                    'contact_2' => $orderData[$eachRow][6],
                    'quantity' => $orderData[$eachRow][7],
                    'totalAmount' => ($resell_product['price'] * $orderData[$eachRow][7]),
                    'paymentMethod' => $orderData[$eachRow][8],
                    'bankSlip' => null,
                    'isResellerCompleted' => 0,
                    'createTime' => $this->AppHelper->get_date_and_time()
                ];
    
                // Insert into OrderItemsTmp
                $orderItemsTmpLog = $this->OrderItemsTmp->add_log($orderDataInfo);
    
                if ($orderItemsTmpLog) {
                    // Prepare orderTmp data
                    $orderTmpInfo = [
                        'resellerId' => $reseller->id,
                        'order' => $orderId,
                        'totalAmount' => ($resell_product['price'] * $orderData[$eachRow][7]) + 350,
                        'paymentMethod' => $orderData[$eachRow][8],
                        'isResellerCompleted' => 0,
                        'createTime' => $this->AppHelper->get_date_and_time(),
                        'remarkInfo' => null,
                        'bankSlip' => null
                    ];
    
                    // Insert into OrderTmp
                    $this->OrderTmp->add_log($orderTmpInfo);
                }
    
            } catch (\Exception $e) {
                // Log the error if any exception occurs
                $errorInfo = [
                    'rowNumber' => $runningRowNumber,
                    'errorMsg' => $e->getMessage(),
                    'createTime' => $this->AppHelper->day_time()
                ];
    
                $this->OrderErr->add_log($errorInfo);
    
                // Continue with the next record
                continue;
            }
        }
    
        return true;
    }      

    private function generateOrderNumber($length) {
        $finalOrderNumber = '';

        while (true) {
            for ($i = 0; $i < $length; $i++) {
                $finalOrderNumber .= random_int(0, 9);
            }
    
            $isOrderNumberAlreadyIn = $this->OrderItems->get_order_by_order_number($finalOrderNumber);
            
            if ($isOrderNumberAlreadyIn == null || empty($isOrderNumberAlreadyIn)) {
                break;
            } else {
                continue;
            }
        }

        return $finalOrderNumber;
    }

    private function validateCity($city) {
        $is_city_valid = false;
        $city_info = $this->CityList->find_by_city($city);

        if ($city_info) {
            $is_city_valid = true;
        }

        return $is_city_valid;
    }

    private function clearTables() {
        DB::table((new ExcelOrderTmp())->getTable())->truncate();
        DB::table((new ExcelOrderItemsTmp())->getTable())->truncate();
        DB::table((new ExcelOrderError())->getTable())->truncate();
    }
}
