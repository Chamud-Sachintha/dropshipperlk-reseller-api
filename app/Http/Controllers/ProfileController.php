<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Reseller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    private $AppHelper;
    private $Reseller;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Reseller = new Reseller();
    }

    public function getSellerProfileInfo(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $resp = $this->Reseller->find_by_token($request_token);

                $profileData = array();

                if ($resp) {
                    
                    $profileData['fullName'] = $resp['full_name'];
                    $profileData['address'] = $resp['address'];
                    $profileData['buisnessName'] = $resp['b_name'];
                    $profileData['phoneNumber'] = $resp['phone_number'];
                    $profileData['nicNumber'] = $resp['nic_number'];
                    $profileData['email'] = $resp['email'];

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

    }
}
