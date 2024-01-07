<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Category;
use App\Models\Product;
use App\Models\Reseller;
use App\Models\ResellProduct;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private $AppHelper;
    private $Product;
    private $Category;
    private $ResellProduct;
    private $Reseller;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Product = new Product();
        $this->Category = new Category();
        $this->ResellProduct = new ResellProduct();
        $this->Reseller = new Reseller();
    }

    public function getAllProductList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $resp = $this->Product->get_all_products();

                $dataList = array();
                foreach ($resp as $key => $value) {
                    $category = $this->Category->find_by_id($value['category']);

                    $dataList[$key]['id'] = $value['id'];
                    $dataList[$key]['productName'] = $value['product_name'];
                    $dataList[$key]['categoryName'] = $category->category_name;
                    $dataList[$key]['description'] = $value['description'];
                    $dataList[$key]['price'] = $value['price'];
                    $dataList[$key]['createTime'] = $value['create_time'];
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getProductInfoByProductId(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $productId = (is_null($request->pid) || empty($request->pid)) ? "" : $request->pid;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($productId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Product Id is required.");
        } else {

            try {
                $resp = $this->Product->find_by_id($productId);
                $reseller = $this->Reseller->find_by_token($request_token);

                if ($resp) {
                    $dataList = array();

                    $categoryInfo = $this->Category->find_by_id($resp->category);
                    $resel_resp = $this->ResellProduct->find_by_pid_and_sid($reseller->id, $productId);

                    $dataList['id'] = $resp['id'];
                    $dataList['productName'] = $resp['product_name'];
                    $dataList['cetagoryName'] = $categoryInfo->category_name;
                    $dataList['description'] = $resp['description'];
                    $dataList['price'] = $resp['price'];
                    $dataList['waranty'] = $resp['waranty'];
                    $dataList['teamCommision'] = $resp['team_commision'];
                    $dataList['directCommision'] = $resp['direct_commision'];

                    if ($resp['stock_count'] > 0) {
                        $dataList['inStock'] = true;
                    } else {
                        $dataList['inStock'] = false;
                    }

                    if ($resel_resp) {
                        $dataList['isResell'] = true;
                    } else {
                        $dataList['isResell'] = false;
                    }

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);

                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Product ID");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
