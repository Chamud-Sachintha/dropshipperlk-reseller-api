<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\KYCInformation;
use App\Models\Reseller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    private $AppHelper;
    private $Reseller;
    private $KYCInfo;

    public function __construct()
    {
        $this->Reseller = new Reseller();
        $this->AppHelper = new AppHelper();
        $this->KYCInfo = new KYCInformation();
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
                $validate_phone = $this->Reseller->find_by_phone($phoneNumber);

                if (!empty($validate_phone)) {
                    return $this->AppHelper->responseMessageHandle(0, "Already Registred Phone Number.");
                }

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

    public function authenticateUser(Request $request) {

        $userName = (is_null($request->userName) || empty($request->userName)) ? "" : $request->userName;
        $password = (is_null($request->password) || empty($request->password)) ? "" : $request->password;

        if ($userName == "") {
            return $this->AppHelper->responseMessageHandle(0, "Username is required.");
        } else if ($password == "") {
            return $this->AppHelper->responseMessageHandle(0, "Password is required.");
        } else {

            try {   
                $resp = $this->Reseller->find_by_phone($userName);

                if ($resp && Hash::check($password, $resp['password'])) {

                    $token = $this->AppHelper->generateAuthToken($resp);

                    $tokenInfo = array();
                    $tokenInfo['token'] = $token;
                    $tokenInfo['loginTime'] = $this->AppHelper->day_time();
                    $token_time = $this->Reseller->update_login_token($resp['id'], $tokenInfo);

                    $isProfileOk = $this->checkProfile($resp->id);

                    if ($isProfileOk == 0) {
                        return $this->AppHelper->responseEntityHandle(2, "Please Submit Your KYC Informations", $resp, $token);
                    } else if ($isProfileOk == 1) {
                        return $this->AppHelper->responseEntityHandle(3, "KYC Still Pending", $resp);
                    } else {
                        return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $resp);
                    }
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Credentials");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    private function checkProfile($clientId) {

        /*
            0 - not submited
            1 - pending
            2 - approved
        */

        try {
            $resp = $this->KYCInfo->get_kyc_by_uid($clientId);

            if (!empty($resp)) {
                if ($resp->status != 1) {
                    return 1;
                } else {
                    return 2;
                }
            } else {
                return 0;
            }
        } catch (\Exception $e) {
            return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
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
