<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Category;
use App\Models\Product;
use App\Models\Reseller;
use App\Models\ResellProduct;
use Illuminate\Http\Request;

class ResellProductController extends Controller
{
    private $AppHelper;
    private $Product;
    private $ResellProduct;
    private $Reseller;
    private $Category;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Product = new Product();
        $this->ResellProduct = new ResellProduct();
        $this->Reseller = new Reseller();
        $this->Category = new Category();
    }

    public function addNewResellProduct(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $productId = (is_null($request->pid) || empty($request->pid)) ? "" : $request->pid;
        $resellPrice = (is_null($request->resellPrice) || empty($request->resellPrice)) ? "" : $request->resellPrice;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($productId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Flag is required.");
        } else if ($resellPrice == "") {
            return $this->AppHelper->responseMessageHandle(0, "Resell Price is required.");
        } else {

            try {
                $product = $this->Product->find_by_id($productId);
                $reseller = $this->Reseller->find_by_token($request_token);

                if ($product) {
                    $resellInfo = array();
                    $resellInfo['resellerId'] = $reseller->id;
                    $resellInfo['productId'] = $productId;
                    $resellInfo['price'] = $resellPrice;
                    $resellInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $resell_product = $this->ResellProduct->add_log($resellInfo);

                    if ($resell_product) {
                        return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                    }
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getAllResellProducts(Request $request) {
        
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $reseller = $this->Reseller->find_by_token($request_token);
                $resp = $this->ResellProduct->get_all($reseller->id);

                $dataList = array();
                foreach ($resp as $key => $value) {

                    $product_info = $this->Product->find_by_id($value['product_id']);
                    $category_info = $this->Category->find_by_id($product_info->category);

                    $dataList[$key]['productId'] = $value['product_id'];
                    $dataList[$key]['productName'] = $product_info['product_name'];
                    $dataList[$key]['categoryName'] = $category_info->category_name;
                    $dataList[$key]['description'] = substr($product_info['description'], 0, 40) . "...";
                    $dataList[$key]['price'] = $product_info['price'];

                    if ($value['status'] == 1) {
                        $dataList[$key]['status'] = "Active";
                    } else {
                        $dataList[$key]['status'] = "Inactive";
                    }

                    $dataList[$key]['resellPrice'] = $value['price'];
                    $dataList[$key]['createTime'] = $product_info['create_time'];
                    $dataList[$key]['resellTime'] = $value['create_time'];

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
