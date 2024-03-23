<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderCancle;
use App\Models\OrderEn;
use App\Models\Product;
use App\Models\Reseller;
use App\Models\ResellProduct;
use App\Models\BankDetails;
use Illuminate\Http\Request;

class BankDetailsController extends Controller
{
    private $Reseller;
    private $AppHelper;
    private $Order;
    private $Product;
    private $ResellProduct;
    private $Category;
    private $OrderCancleLog;
    private $OrderEn;
    private $BankDetails;

    public function __construct()
    {
        $this->Reseller = new Reseller();
        $this->AppHelper = new AppHelper();
        $this->Order = new Order();
        $this->Product = new Product();
        $this->ResellProduct = new ResellProduct();
        $this->Category = new Category();
        $this->OrderCancleLog = new OrderCancle();
        $this->OrderEn = new  OrderEn();
        $this->BankDetails = new  BankDetails();
    }

    public function UpdateBankInfo(Request $request){

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
       

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        }else
         {

            try {
            $resellerId = $this->Reseller->find_by_token($request_token);
            if($resellerId)
            {
                $bankdata =array();
                $bankdata['reselller_id']=$resellerId->id;
                $bankdata['bank_name']=$request->BankName;
                $bankdata['account_number']=$request->AccountNumber;
                $bankdata['branch_code']=$request->BranchCode;
                $bankdata['resellr_name']=$request->Name;
                $bankdata['createTime'] = $this->AppHelper->get_date_and_time();
               
                $banksuccess = $this->BankDetails->Add_data($bankdata);

                if ($banksuccess) {
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

    public function UpdateEditBankInfo(Request $request){
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
       

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        }else
         {

            try {
            $resellerId = $this->Reseller->find_by_token($request_token);
            $bankdata = $this->BankDetails->find_id($resellerId->id);
            $Bid =  $bankdata->id;
            if($resellerId)
            {
                $bankdata =array();
                $bankdata['bank_name']=$request->BankName;
                $bankdata['account_number']= (int)$request->AccountNumber;
                $bankdata['branch_code']=$request->BranchCode;
                $bankdata['resellr_name']=$request->Name;
                $bankdata['createTime'] = $this->AppHelper->get_date_and_time();
               
                $banksuccess = $this->BankDetails->update_data($Bid, $bankdata);

                if ($banksuccess) {
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
    
}
