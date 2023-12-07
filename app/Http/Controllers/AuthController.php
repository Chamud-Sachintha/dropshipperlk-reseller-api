<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Reseller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private $AppHelper;
    private $Reseller;

    public function __construct()
    {
        $this->Reseller = new Reseller();
        $this->AppHelper = new AppHelper();
    }

    public function registerNewReseller(Request $request) {

        $fullName = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $bName = (is_null($request->bName) || empty($request->bName)) ? "" : $request->bName;
        $address = (is_null($request->address) || empty($request->address)) ? "" : $request->address;
        $phoneNumber = (is_null($request->phoneNumber) || empty($request->phoneNumber)) ? "" : $request->phoneNumber;
        $nicNumbe = (is_null($request->nicNumber) || empty($request->nicNumber)) ? "" : $request->nicNumber;
        $email = (is_null($request->email) || empty($request->email)) ? "" : $request->email;
        $password = (is_null($request->password) || empty($request->password)) ? "" : $request->password;
        $refCode = (is_null($request->refCode) || empty($request->refCode)) ? "" : $request->refCode;

        if ($fullName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Full Name is required.");
        } else if ($bName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Buisness Name is required.");
        } else if ($phoneNumber == "") {
            return $this->AppHelper->responseMessageHandle(0, "Phone Number is required.");
        } else if ($nicNumbe == "") {
            return $this->AppHelper->responseMessageHandle(0, "NIC Number is required.");
        } else {

            try {
                $validate_ref = $this->Reseller->validate_ref_code($refCode);

                if (empty($validate_ref)) {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Ref Code.");
                }

                $refLimit = $this->checkRefLimit($refCode);

                $sellerInfo = array();

                if ($refLimit) {
                    $sellerInfo['fullName'] = $fullName;
                    $sellerInfo['bName'] = $bName;
                    $sellerInfo['address'] = $address;
                    $sellerInfo['phoneNumber'] = $phoneNumber;
                    $sellerInfo['nicNumber'] = $nicNumbe;
                    $sellerInfo['email'] = $email;
                    $sellerInfo['password'] = Hash::make($password);
                    $sellerInfo['refCode'] = $refCode;
                    $sellerInfo['code'] = Str::random(5);
                    $sellerInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $resellerResp = $this->Reseller->add_log($sellerInfo);

                    if ($resellerResp) {
                        return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $resellerResp);
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Referal Limit is Exceeded.");
                }   

            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    private function checkRefLimit($refCode) {

        $isLimitOk = true;

        try {
            $resp = $this->Reseller->get_count_by_ref_code($refCode);

            if ($resp == 3) {
                $isLimitOk = false;
            }
        } catch (\Exception $e) {
            return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
        }

        return $isLimitOk;
    }
}
