<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Order;
use App\Models\ProfitShare;
use App\Models\Reseller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private $AppHelper;
    private $Orders;
    private $Reseller;
    private $ProfitShareLog;
    
    public function __construct()
    {   
        $this->AppHelper = new AppHelper();
        $this->Reseller = new Reseller();
        $this->Orders = new Order();
        $this->ProfitShareLog = new ProfitShare();
    }

    public function getDashboardData(Request $request) {

        $dataList = array();
        $seller_info = $this->Reseller->find_by_token($request->token);

        if ($seller_info) {
            $pending_orders = $this->Orders->get_pending_count_by_seller($seller_info['id']);
            $in_courier_orders = $this->Orders->get_in_courier_count_by_seller($seller_info['id']);
            $delivered_corders = $this->Orders->get_complete_count_by_seller($seller_info['id']);
            $total_orders = $this->Orders->get_total_orders($seller_info['id']);
            $cancle_orders = $this->Orders->get_camcle_count_by_seller($seller_info['id']);
            $paid_orders = $this->Orders->get_paid_order_count($seller_info['id']);
            $received_earnings = $this->ProfitShareLog->get_total_earnings($seller_info['id']);
            $pending_payment = $this->Orders->get_pending_payment($seller_info['id']);

            $dataList['pendingOrderCount'] = $pending_orders;
            $dataList['inCourierOrderCount'] = $in_courier_orders;
            $dataList['completeOrderCount'] = $delivered_corders;
            $dataList['totalOrders'] = $total_orders;
            $dataList['cancleOrders'] = $cancle_orders;
            $dataList['paidOrders'] = $paid_orders;
            $dataList['totalEarnigs'] = $received_earnings;
            $dataList['pendingPayment'] = $pending_payment;
            $dataList['refCode'] = $seller_info['code'];

            return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Sellert ID");
        }

    }
}
