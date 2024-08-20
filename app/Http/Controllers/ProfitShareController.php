<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Order;
use App\Models\OrderEn;
use App\Models\Product;
use App\Models\ProfitShare;
use App\Models\Reseller;
use Illuminate\Http\Request;

class ProfitShareController extends Controller
{
    private $AppHelper;
    private $ProfitShareLog;
    private $Reseller;
    private $Product;
    private $Order;
    private $OrderEn;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->ProfitShareLog = new ProfitShare();
        $this->Reseller = new Reseller();
        $this->Product = new Product();
        $this->Order = new Order();
        $this->OrderEn = new OrderEn();
    }

    public function getProfitShareLogBySeller(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        
        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required");
        } else {

            // try {
                $seller_info = $this->Reseller->find_by_token($request_token);
                $resp = $this->ProfitShareLog->get_log_by_seller($seller_info['id']);

                $dataList = array();
                foreach ($resp as $key => $value) {
                    $product_info = $this->Product->find_by_id($value['product_id']);
                    $order_info = null;
                    if($value['order_id'] != 0){
                        $order_info = $this->Order->find_by_id($value['order_id']);
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
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            // } catch (\Exception $e) {
            //     return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            // }
        }
    }
}
