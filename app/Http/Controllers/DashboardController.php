<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Order;
use App\Models\ProfitShare;
use App\Models\Reseller;
use Illuminate\Http\Request;
use App\Models\OrderEn;

class DashboardController extends Controller
{
    private $AppHelper;
    private $Orders;
    private $Reseller;
    private $ProfitShareLog;
    private $OrderEn;
    
    public function __construct()
    {   
        $this->AppHelper = new AppHelper();
        $this->Reseller = new Reseller();
        $this->Orders = new Order();
        $this->ProfitShareLog = new ProfitShare();
        $this->OrderEn = new  OrderEn();
    }

    public function getDashboardData(Request $request) {

        $dataList = array();
        $seller_info = $this->Reseller->find_by_token($request->token);

        if ($seller_info) {
            $pending_orders = $this->OrderEn->get_pending_count_by_seller($seller_info['id']);
            $in_courier_orders = $this->OrderEn->get_in_courier_count_by_seller($seller_info['id']);
            $complete_corders = $this->OrderEn->get_complete_count_by_seller($seller_info['id']);
            $delivered_corders = $this->OrderEn->get_pending_count($seller_info['id']);
            $total_orders = $this->OrderEn->get_total_orders($seller_info['id']);
            $cancle_orders = $this->OrderEn->get_camcle_count_by_seller($seller_info['id']);
            $paid_orders = $this->OrderEn->get_paid_order_count($seller_info['id']);
            $received_earnings = $this->ProfitShareLog->get_total_earnings($seller_info['id']);
            $pending_payment = $this->Reseller->get_pending_payout($seller_info['id']);

            $total_team_commision = $this->ProfitShareLog->get_team_com_by_seller($seller_info['id']);
            $total_direct_commision = $this->ProfitShareLog->get_direct_com_by_seller($seller_info['id']);
            $team_count = $this->Reseller->get_team_by_ref_code_count($seller_info['code']);

            $hold_orders = $this->OrderEn->get_hold_order_count($seller_info['id']);
            $return_orderds = $this->OrderEn->get_return_order_count($seller_info['id']);

            $dataList['pendingOrderCount'] = $pending_orders;
            $dataList['inCourierOrderCount'] = $in_courier_orders;
            $dataList['completeOrderCount'] = $complete_corders;
            $dataList['totalOrders'] = $total_orders;
            $dataList['cancleOrders'] = $cancle_orders;
            $dataList['paidOrders'] = $paid_orders;
            $dataList['totalEarnigs'] = $received_earnings;
            $dataList['pendingPayment'] = $pending_payment;
            $dataList['Deleriverd'] = $delivered_corders;
            $dataList['refCode'] = $seller_info['code'];

            $dataList['holdOrderCount'] = $hold_orders;
            $dataList['returnOrderCount'] = $return_orderds;

            $dataList['teamCommisionTotal'] = $total_team_commision;
            $dataList['directCommisionTotal'] = $total_direct_commision;
            $dataList['teamCount'] = $team_count;

            return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
        } else {
            return $this->AppHelper->responseMessageHandle(0, "Invalid Sellert ID");
        }

    }

    public function Getuserdata(Request $request){
        
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {
            $userdata =  $this->Reseller->find_by_token( $request_token);
            $UserName = $userdata['full_name'];
            return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $UserName);
        }

    }
}
