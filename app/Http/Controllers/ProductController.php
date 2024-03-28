<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Category;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
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
    private $Cart;
    private $CartItem;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Product = new Product();
        $this->Category = new Category();
        $this->ResellProduct = new ResellProduct();
        $this->Reseller = new Reseller();
        $this->CartItem = new CartItem();
        $this->Cart = new Cart();
    }

    public function getAllProductList(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $resp = $this->Product->get_all_products();
                $AllCategory = Category::get();

                $dataList = array();
                foreach ($resp as $key => $value) {
                    $category = $this->Category->find_by_id($value['category']);

                    $dataList[$key]['id'] = $value['id'];
                    $dataList[$key]['productName'] = $value['product_name'];
                    $dataList[$key]['categoryName'] = $category->category_name;
                    $dataList[$key]['description'] = $value['description'];
                    $dataList[$key]['price'] = $value['price'];
                    $dataList[$key]['images'] = json_decode($value['images'])->image0;
                    $dataList[$key]['createTime'] = $value['create_time'];

                }
                
                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList, $AllCategory);
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
                    $dataList['productWeigth'] = $resp['weight'];
                    $dataList['cetagoryName'] = $categoryInfo->category_name;
                    $dataList['description'] = $resp['description'];
                    $dataList['price'] = $resp['price'];
                    $dataList['waranty'] = $resp['waranty'];
                    $dataList['teamCommision'] = $resp['team_commision'];
                    $dataList['directCommision'] = $resp['direct_commision'];
                    $dataList['images']= json_decode($resp->images);
                    $dataList['Stock']= $resp['stock_count'];

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

                    $in_colombo_fees = $this->getCourierCharge(true, $resp['weight']);
                    $out_of_colombo_fees = $this->getCourierCharge(false, $resp['weight']);

                    $dataList['in_colombo_charges'] = $in_colombo_fees;
                    $dataList['out_of_colombo_charges'] = $out_of_colombo_fees;

                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);

                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Invalid Product ID");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getAllResellProductsDeliverycharg(Request $request){
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
       
        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        }
        else 
        {
           
           try {    
                    $dataList = array();
                    $sellerID = Reseller::where('token',$request_token)->pluck('id');
                    $CartId = Cart::where('seller_id', $sellerID)->pluck('id');
                    $productIds = CartItem::where('cart_id',$CartId) ->select('product_id') ->distinct() ->pluck('product_id');
                    $totalWeight = Product::whereIn('id', $productIds)->sum('weight');

                    $in_colombo_fees = $this->getCourierCharge(true, $totalWeight);
                    $out_of_colombo_fees = $this->getCourierCharge(false, $totalWeight);
                    $dataList['in_colombo_charges'] = $in_colombo_fees;
                    $dataList['out_of_colombo_charges'] = $out_of_colombo_fees;
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
                  
               
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
            
            
        }
    }

    public function getAllResellProductsDeliverychargProId(Request $request){
        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
       
        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        }
        else 
        {
           
           try {    
                    $dataList = array();
                    $productIds = $request->productId;
                    $totalWeight = Product::where('id', $productIds)->pluck('weight')->first();
                    $in_colombo_fees = $this->getCourierCharge(true, $totalWeight);
                    $out_of_colombo_fees = $this->getCourierCharge(false, $totalWeight);
                    $dataList['in_colombo_charges'] = $in_colombo_fees;
                    $dataList['out_of_colombo_charges'] = $out_of_colombo_fees;
                    return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
                  
               
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
            
            
        }
    }

    private function getCourierCharge($is_colombo, $product_weight) {

        $default_charge = 300;
        $weight_in_kg = ($product_weight) / 1000;

        if ($weight_in_kg > 1) {
            $remaining = $weight_in_kg - 1;
            $round_remaining = ceil($remaining);
            
            if ($round_remaining > 0) {
                $default_charge += ($round_remaining * 50);
            }
        }

        if (!$is_colombo) {
            $default_charge += 50;
        }

        return $default_charge;
    }

    public function getCIDProductList(Request $request){

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $resp = $this->Product->find_by_Cid($request->Cid);
                $AllCategory = Category::get();


                $dataList = array();
                foreach ($resp as $key => $value) {
                    $category = $this->Category->find_by_id($value['category']);

                    $dataList[$key]['id'] = $value['id'];
                    $dataList[$key]['productName'] = $value['product_name'];
                    $dataList[$key]['categoryName'] = $category->category_name;
                    $dataList[$key]['description'] = $value['description'];
                    $dataList[$key]['price'] = $value['price'];
                    $dataList[$key]['images'] = json_decode($value['images'])->image0;
                    $dataList[$key]['createTime'] = $value['create_time'];

                }
                
                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList, $AllCategory);
                
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
