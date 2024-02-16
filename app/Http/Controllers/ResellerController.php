<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Reseller;
use Illuminate\Http\Request;

class ResellerController extends Controller
{
    private $AppHelper;
    private $Reseller;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Reseller = new Reseller();
    }

    public function getTeam(Request $request) {
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $reseller_info = $this->Reseller->find_by_token($request_token);

                $team = $this->Reseller->find_by_ref_code($reseller_info->code);

                $dataList = array();
                foreach ($team as $key => $value) {
                    $dataList[$key]['fullName'] = $value['full_name'];
                    $dataList[$key]['bName'] = $value['b_name'];
                    $dataList[$key]['phoneNumber'] = $value['phone_number'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
