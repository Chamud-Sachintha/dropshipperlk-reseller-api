<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Product;
use App\Models\Reseller;
use App\Models\OrderEn;
use App\Models\OrderCancle;
use App\Models\BankDetails;
use App\Models\InCourierDetail;
use App\Models\ProfitShare;
use App\Models\ResellProduct;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Facades\Excel;

use function PHPUnit\Framework\returnSelf;

class SimpleExcelExport implements FromCollection
{
    private $selectedReportType;
    private $sellerID;
    /**
     * @return \Illuminate\Support\Collection
     */

    private $Orders;
    private $Reseller;
    private $InCourier;
    private $ResellProduct;
    private $ProfitShareLog;
    private $Product;
    private $OrderEn;

    public function __construct($selectedReportType, $sellerID)
    {
        $this->selectedReportType = $selectedReportType;
        $this->sellerID = $sellerID;
        $this->Orders = new Order();
        $this->Reseller = new Reseller();
        $this->InCourier = new InCourierDetail();
        $this->ResellProduct = new ResellProduct();
        $this->ProfitShareLog = new ProfitShare();
        $this->Product = new Product();
        $this->OrderEn = new OrderEn();
    }

    public function collection()
    {
        switch ($this->selectedReportType) {
            case 1:
                return $this->getOrdersReport($this->sellerID);
            case 2:
                return $this->getCommisionReport($this->sellerID);
        }
    }

    private function getCommisionReport($sellerId) {
        $seller_info = $this->Reseller->find_by_id($sellerId);
        $resp = $this->ProfitShareLog->get_log_by_seller($seller_info['id']);

        $dataList = array();
        foreach ($resp as $key => $value) {
            $product_info = $this->Product->find_by_id($value['product_id']);
            $order_info = null;
            if($value['order_id'] != 0){
                $order_info = $this->Orders->find_by_id($value['order_id']);
                $dataList[$key]['orderNumber'] =  $order_info['order'];
            }
            else{
                
                $dataList[$key]['orderNumber'] =  "-";
            }

            

            if ($value['product_id'] != 0) {
                //  $dataList[$key]['productName'] = $product_info['product_name'];
                $dataList[$key]['productName'] = $product_info['product_name'];
            } else {
                $dataList[$key]['productName'] = 0;
            }

            if ($value['type'] == 1) {
                $dataList[$key]['logType'] = "Transfer In";
            } else {
                $dataList[$key]['logType'] = "Transfer Out";
            }

            if ($product_info != null) {
                $dataList[$key]['productPrice'] = $product_info['price'];
            } else {
                $dataList[$key]['productPrice'] = "Not Found";
            }

            $dataList[$key]['deliveryCharge'] = 0;

            if ($order_info != null) {
                $orderEnInfo = $this->OrderEn->getOrderInfoByOrderNumber($order_info['order']);

                if ($orderEnInfo['payment_method'] != 3) {
                    $dataList[$key]['deliveryCharge'] = 350;
                }
            }

            $dataList[$key]['resellPrice'] = $value['resell_price'];
            $dataList[$key]['quantity'] = $value['quantity'];
            $dataList[$key]['totalAmount'] = $value['total_amount'];
            $dataList[$key]['profit'] = $value['profit'];
            $dataList[$key]['directCommision'] = $value['direct_commision'];
            $dataList[$key]['teamCommision'] = $value['team_commision'];
            $dataList[$key]['profitTotal'] = $value['profit_total'];
            $dataList[$key]['date'] = date('Y-m-d', $value['create_time']);
        }

        $headers = [
            'Order ID', 'Product Name', 'Log Type', 'Product Price', 'Deliver Charge', 'Resell Price', 'Quantity', 'Total Amount', 'Direct Commision', 'Team Commision'
            ,   'Profit', 'Profit Total', 'Create Date'
        ];

        $collection = collect($dataList);
        $collection->prepend($headers);

        return $collection;
    }

    private function getOrdersReport($sellerID)
    {
        $orders = Order::with(['product', 'orderEn'])
                    ->whereHas('orderEn', function ($query) use ($sellerID) {
                        $query->where('reseller_id', $sellerID);
                    })
                    ->get();

        $dataArray = $orders->map(function ($order) {
            try {
                $courier_info = $this->InCourier->find_by_order_id($order->order);
                $resell_product_info = $this->ResellProduct->find_by_pid_and_sid($order->orderEn->reseller_id, $order->product->id);

                $product = $order->product;
                $orderEn = $order->orderEn;

                $prefix = substr($order->city, 0, 3);
                $is_colombo = $prefix === "Col";

                $courier_charge = 0;
                
                if ($order->orderEn->payment_method != 3) {
                    $courier_charge = $this->getCourierCharge($is_colombo, $product->weight);
                }

                $fullAmount = $order->total_amount + $courier_charge;

                $statusMapping = [
                    0 => "Pending",
                    1 => "Hold",
                    2 => "Packaging",
                    3 => "Cancel",
                    4 => "In Courier",
                    5 => "Delivered",
                    6 => "Return Order",
                    7 => "Complete",
                    8 => "Settled",
                    9 => "Return Recieved",
                    10 => "Rider Assigned",
                    11 => "Rescheduled"
                ];

                $status = $statusMapping[$orderEn->order_status] ?? "Unknown";

                $refundStatus = $orderEn->order_status == 6 && $orderEn->return_status == 1 ? "Refunded" : "No Refund";

                $wayBillNo = null;
                if ($courier_info != null) {
                    $wayBillNo = $courier_info->way_bill;
                } else {
                    $wayBillNo = "-";
                }

                return [
                    'Order' => $order->order,
                    'Product Name' => $product->product_name,
                    'Product Price' => $product->price,
                    'Delivery Charge' => '350',
                    'Seller Price' => $resell_product_info->price,
                    'Tracking No' => $orderEn->tracking_number,
                    'Courier Name' => $orderEn->courier_name,
                    'Order Status' => $status,
                    'Name' => $order->name,
                    'Address' => $order->address,
                    'City' => $order->city,
                    'District' => $order->district,
                    'Contact 1' => $order->contact_1,
                    'Contact 2' => $order->contact_2,
                    'Quantity' => $order->quantity,
                    'Total Amount' => $fullAmount,
                    'Order Return Status' => $refundStatus,
                    'WayBill' => $wayBillNo,
                    'Date' => date('Y-m-d', $order->orderEn->create_time)
                ];
            } catch (\Exception $e) {
                // Log the error and skip this order
                // \Log::error('Error processing order ID: ' . $order->id . ' - ' . $e->getMessage());
                return null; // Skip this order
            }   
        })->filter(); // Filter out any null entries

        $headers = [
            'Order ID', 'Product Name', 'Product Price', 'Delivery Charge', 'Seller Price', 'Tracking No', 'Courier Name', 'Order Status', 'Name', 'Address',
            'City', 'District', 'Contact 1', 'Contact 2', 'Quantity', 'Total Amount', 'Order Return Status','WayBill', 'Date'
        ];

        $dataArray->prepend($headers);

        return $dataArray;
    }

    private function getCourierCharge($is_colombo, $product_weight)
    {
        $default_charge = 350;

        if (!is_numeric($product_weight)) {
            throw new InvalidArgumentException("Product weight must be a numeric value.");
        }

        $weight_in_kg = $product_weight / 1000;
        if ($weight_in_kg > 1) {
            $remaining = ceil($weight_in_kg - 1);
            $default_charge += $remaining * 50;
        }

        // if (!$is_colombo) {
        //     $default_charge += 50;
        // }

        return $default_charge;
    }
}
