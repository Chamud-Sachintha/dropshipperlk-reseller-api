<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Reseller;
use App\Models\BankDetails;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    private $AppHelper;
    private $Reseller;
    private $BankDetails;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Reseller = new Reseller();
        $this->BankDetails = new  BankDetails();
    }

    public function getSellerProfileInfo(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $resp = $this->Reseller->find_by_token($request_token);
                $respBankData = $this->BankDetails->find_id($resp->id);

                $profileData = array();

                if ($resp) {
                    
                    $profileData['fullName'] = $resp['full_name'];
                    $profileData['address'] = $resp['address'];
                    $profileData['buisnessName'] = $resp['b_name'];
                    $profileData['phoneNumber'] = $resp['phone_number'];
                    $profileData['nicNumber'] = $resp['nic_number'];
                    $profileData['email'] = $resp['email'];
                    $profileData['bank_name'] = $respBankData['bank_name'] ?? '';
                    $profileData['account_number'] = $respBankData['account_number'] ?? '';
                    $profileData['resellr_name'] = $respBankData['resellr_name'] ?? '';
                    $profileData['branch_code'] = $respBankData['branch_code'] ?? '';

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $profileData);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "There is No Profile data.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function updateProfileData(Request $request) {
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $fullName = (is_null($request->fullName) || empty($request->fullName)) ? "" : $request->fullName;
        $address = (is_null($request->address) || empty($request->address)) ? "" : $request->address;
        $buisnessName = (is_null($request->buisnessName) || empty($request->buisnessName)) ? "" : $request->buisnessName;
        $email = (is_null($request->email) || empty($request->email)) ? "" : $request->email;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {
            try {
                $resp = $this->Reseller->find_by_token($request_token);
                $sellerid = $resp['id'];

                $profileData = array();
                   
                    $profileData['fullName'] = $fullName;
                    $profileData['address'] = $address;
                    $profileData['buisnessName'] = $buisnessName;
                    $profileData['email'] = $email;

                    $updated = $this->Reseller->update_by_token($sellerid,$profileData);
                if( $updated){
                   
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $updated);
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "There is No Profile data.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
