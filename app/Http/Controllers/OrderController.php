<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderCancle;
use App\Models\Product;
use App\Models\Reseller;
use App\Models\ResellProduct;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $Reseller;
    private $AppHelper;
    private $Order;
    private $Product;
    private $ResellProduct;
    private $Category;
    private $OrderCancleLog;

    public function __construct()
    {
        $this->Reseller = new Reseller();
        $this->AppHelper = new AppHelper();
        $this->Order = new Order();
        $this->Product = new Product();
        $this->ResellProduct = new ResellProduct();
        $this->Category = new Category();
        $this->OrderCancleLog = new OrderCancle();
    }

    public function placeNewOrderRequest(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $productId = (is_null($request->pid) || empty($request->pid)) ? "" : $request->pid;
        $name = (is_null($request->name) || empty($request->name)) ? "" : $request->name;
        $address = (is_null($request->address) || empty($request->address)) ? "" : $request->address;
        $city = (is_null($request->city) || empty($request->city)) ? "" : $request->city;
        $district = (is_null($request->district) || empty($request->district)) ? "" : $request->district;
        $f_contact = (is_null($request->firstContact) || empty($request->firstContact)) ? "" : $request->firstContact;
        $s_contact = (is_null($request->secondContact) || empty($request->secondContact)) ? "" : $request->secondContact;
        $paymentMethod = (is_null($request->paymentMethod) || empty($request->paymentMethod)) ? "" : $request->paymentMethod;
        $quantity = (is_null($request->quantity) || empty($request->quantity)) ? "" : $request->quantity;
        $bankSlip = (is_null($request->bankSlip) || empty($request->bankSlip)) ? "" : $request->bankSlip;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($productId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Product ID is required.");
        } else if ($name == "") {
            return $this->AppHelper->responseMessageHandle(0, "Name is required.");
        } else if ($address == "") {
            return $this->AppHelper->responseMessageHandle(0, "Address is required.");
        } else if ($city == "") {
            return $this->AppHelper->responseMessageHandle(0, "City is required.");
        } else if ($district == "") {
            return $this->AppHelper->responseMessageHandle(0, "District is required.");
        } else if ($f_contact == "") {
            return $this->AppHelper->responseMessageHandle(0, "Contact 1 is required.");
        } else if ($s_contact == "") {
            return $this->AppHelper->responseMessageHandle(0, "Contact 2 is required.");
        } else if ($paymentMethod == "") {
            return $this->AppHelper->responseMessageHandle(0, "Payment Method is required.");
        } else {

            try {
                $reseller = $this->Reseller->find_by_token($request_token);
                $product = $this->Product->find_by_id($productId);
                $resell_product = $this->ResellProduct->find_by_pid_and_sid($reseller->id, $productId);

                if ($product) {
                    $orderInfo = array();
                    $orderInfo['productId'] = $productId;
                    $orderInfo['resellerId'] = $reseller->id;
                    $orderInfo['order'] = $this->AppHelper->generate_ref(10);
                    $orderInfo['name'] = $name;
                    $orderInfo['address'] = $address;
                    $orderInfo['city'] = $city;
                    $orderInfo['district'] = $district;
                    $orderInfo['contact_1'] = $f_contact;
                    $orderInfo['contact_2'] = $s_contact;
                    $orderInfo['quantity'] = $quantity;
                    $orderInfo['totalAmount'] = $resell_product['price'] * $quantity;
                    $orderInfo['paymentMethod'] = $paymentMethod;
                    
                    if ($bankSlip != "") {
                        $orderInfo['bankSlip'] = $this->AppHelper->decodeImage($bankSlip);
                    } else {
                        $orderInfo['bankSlip'] = null;
                    }
                    
                    $orderInfo['isResellerCompleted'] = 0;
                    $orderInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $order = $this->Order->add_log($orderInfo);

                    if ($order) {
                        return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getOrderList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                // $resp = $this->Order->get_all();
                $seller_info = $this->Reseller->find_by_token($request_token);
                $resp = $this->Order->get_by_seller($seller_info->id);

                $dataList = array();
                foreach ($resp as $key => $value) {

                    $product_info = $this->Product->find_by_id($value['product_id']);
                    $resell_info = $this->ResellProduct->find_by_pid_and_sid($value['reseller_id'], $value['product_id']);

                    $dataList[$key]['orderNumber'] = $value['order'];
                    $dataList[$key]['productName'] = $product_info['product_name'];
                    $dataList[$key]['productPrice'] = $product_info['price'];
                    $dataList[$key]['resellPrice'] = $resell_info['price'];
                    $dataList[$key]['quantity'] = $value['quantity'];
                    $dataList[$key]['totalAmount'] = $value['total_amount'];
                    
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
                    } else {
                        $dataList[$key]['orderStatus'] = "Delivered";
                    }

                    $dataList[$key]['orderPlaceDate'] = $value['create_time'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getOrderInfoByOrderNumber(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $orderNumber = (is_null($request->orderNumber) || empty($request->orderNumber)) ? "" : $request->orderNumber;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($orderNumber == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $order_info = $this->Order->get_order_by_order_number($orderNumber);
                
                $dataList = array();
                if ($order_info) {

                    $product_info = $this->Product->find_by_id($order_info['product_id']);
                    $category_info = $this->Category->find_by_id($product_info['category']);

                    $dataList['productName'] = $product_info['product_name'];
                    $dataList['categoryName'] = $category_info['category_name'];
                    $dataList['quantity'] = $order_info['quantity'];
                    $dataList['totalAmount'] = $order_info['total_amount'];
                    $dataList['images'] = json_decode($product_info['images']);

                    if ($order_info['payment_status'] == 0) {
                        $dataList['paymentStatus'] = "Pending";
                    } else if ($order_info['payment_status'] == 1) {
                        $dataList['paymentStatus'] = "Paid";
                    } else {
                        $dataList['paymentStatus'] = "Refunded";
                    }

                    if ($order_info['order_status'] == 0) {
                        $dataList['orderStatus'] = "Pending";
                    } else if ($order_info['order_status'] == 1) {
                        $dataList['orderStatus'] = "Hold";
                    } else if ($order_info['order_status'] == 2) {
                        $dataList['orderStatus'] = "Packaging";
                    } else if ($order_info['order_status'] == 3) {
                        $dataList['orderStatus'] = "Cancle";
                    } else if ($order_info['order_status'] == 4) {
                        $dataList['orderStatus'] = "In Courier";
                    } else {
                        $dataList['orderStatus'] = "Delivered";
                    }

                    $dataList['orderCancled'] = 0;
                    $dataList['cancleOrder'] = 0;

                    if ($order_info['order_status'] < 4) {
                        $dataList['cancleOrder'] = 1;
                    }

                    if ($order_info['order_status'] == 3) {
                        $dataList['orderCancled'] = 1;
                    }

                    $dataList['teamCommision'] = $product_info['team_commision'];
                    $dataList['directCommision'] = $product_info['direct_commision'];
                    $dataList['orderPlaceDate'] = $order_info['create_time'];

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function cancleOrder(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $orderNumber = (is_null($request->orderNumber) || empty($request->orderNumber)) ? "" : $request->orderNumber;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($orderNumber == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $order_info = $this->Order->get_order_by_order_number($orderNumber);

                if ($order_info) {
                    if ($order_info['order_status'] < 4) {
                        $cancel_order = $this->Order->cancle_order_by_number($orderNumber);

                        $cancle_log = null;
                        if ($cancel_order) {
                            $orderCancelLog = array();
                            $orderCancelLog['orderId'] = $order_info['id'];
                            $orderCancelLog['reseller'] = $order_info['reseller_id'];
                            $orderCancelLog['totalAmount'] = $order_info['total_amount'];
                            $orderCancelLog['status'] = 0;
                            $orderCancelLog['createTime'] = $this->AppHelper->get_date_and_time();

                            $cancle_log = $this->OrderCancleLog->add_log($orderCancelLog);
                        }

                        if ($cancel_order && $cancle_log) {
                            return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                        } else {
                            return $this->AppHelper->responseMessageHandle(0, "Error Occired.");
                        }
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Cannot Close Order.");
                    }
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
