<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\ProfitShare;
use App\Models\Reseller;
use Illuminate\Http\Request;

class ProfitShareController extends Controller
{
    private $AppHelper;
    private $ProfitShareLog;
    private $Reseller;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->ProfitShareLog = new ProfitShare();
        $this->Reseller = new Reseller();
    }

    public function getProfitShareLogBySeller(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        
        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required");
        } else {

            try {
                $seller_info = $this->Reseller->find_by_token($request_token);
                $resp = $this->ProfitShareLog->get_log_by_seller($seller_info['id']);

                $dataList = array();
                foreach ($resp as $key => $value) {
                    
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
