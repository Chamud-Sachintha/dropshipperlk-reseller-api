<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\KYCInformation;
use App\Models\Reseller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KYCController extends Controller
{
    private $AppHelper;
    private $KYCModel;
    private $Reseller;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->KYCModel = new KYCInformation();
        $this->Reseller = new Reseller();
    }

    public function addKYCInformation(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $frontImg = (is_null($request->frontImg) || empty($request->frontImg)) ? "" : $request->frontImg;
        $backImg = (is_null($request->backImg) || empty($request->backImg)) ? "" : $request->backImg;

        if ($request_token == "") { 
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $reseller = $this->Reseller->find_by_token($request_token);

                if ($reseller) {
                    $kycInfo = $this->KYCModel->get_kyc_by_uid($reseller->id);

                    if (!empty($kycInfo)) {
                        return $this->AppHelper->responseMessageHandle(0, "KYC Already Submited.");
                    } else {
                        $kycInfo = array();
                        $kycInfo['clientId'] = $reseller->id;
                        $kycInfo['frontImg'] = $this->decodeImage($frontImg);
                        $kycInfo['backImg'] = $this->decodeImage($backImg);
                        $kycInfo['createTime'] = $this->AppHelper->get_date_and_time();
                        $kycInfo['modTime'] = $this->AppHelper->get_date_and_time();

                        $kyc = $this->KYCModel->add_log($kycInfo);

                        if ($kyc) {
                            return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                        } else {
                            return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                        }
                    }
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getSubmitedKycList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $sellerId = (is_null($request->sellerId) || empty($request->sellerId)) ? "" : $request->sellerId;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($sellerId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Seller Id is required.");
        } else {

            try {
                $resp = $this->KYCModel->get_kyc_by_uid($sellerId);

                $dataList = array();
                foreach ($resp as $key => $value) {
                    // $dataList[$key]['']
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }   
        }
    }

    private function decodeImage($imageData) {

        $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));
        $imageFileName = 'image_' . time() . Str::random(5) . '.png';

        // Storage::kyc('kyc')->put($imageFileName, $image);
        file_put_contents(public_path() . '/kyc' . '/' . $imageFileName, $image);

        return $imageFileName;
    }
}
