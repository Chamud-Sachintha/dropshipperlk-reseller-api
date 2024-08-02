<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\city_list;
use Illuminate\Http\Request;

class CityListController extends Controller
{
    private $City;
    private $AppHelper;

    public function __construct()
    {
        $this->City = new city_list();
        $this->AppHelper = new AppHelper();
    }

    public function getAllCityList(Request $request) {
        $city_list = $this->City->query_all();

        $city_info = [];
        foreach ($city_list as $key => $value) {
            $city_info[$key]['cityName'] = $value['city'];
            $city_info[$key]['district'] = $value['district'];
            $city_info[$key]['province'] = $value['province'];
            $city_info[$key]['zipCode'] = $value['zip_code'];
            $city_info[$key]['shippingZone'] = $value['shipping_zone'];
        }

        return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $city_info);
    }
}
