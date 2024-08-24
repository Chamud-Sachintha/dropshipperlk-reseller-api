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

                    if ($value['status'] == 1) {
                        $dataList[$key]['inStock'] = true;
                    } else {
                        $dataList[$key]['inStock'] = false;
                    }

                    $decodedImages = json_decode($value['images']);
                    if ($decodedImages && isset($decodedImages->image0) && !empty($decodedImages->image0)){
                    $dataList[$key]['images'] = json_decode($value['images'])->image0;
                    }
                    else{
                        $dataList[$key]['image'] = '';
                    }
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
                    $dataList['is_store_pick'] = $resp['is_store_pick'];
                    $dataList['teamCommision'] = $resp['team_commision'];
                    $dataList['directCommision'] = $resp['direct_commision'];
                    $dataList['images']= json_decode($resp->images);
                    $dataList['Stock']= $resp['stock_count'];
                    $dataList['StockStatus'] = $resp['status'];
                    
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

        $default_charge = 350;
        $weight_in_kg = ($product_weight) / 1000;

        if ($weight_in_kg > 1) {
            $remaining = $weight_in_kg - 1;
            $round_remaining = ceil($remaining);
            
            if ($round_remaining > 0) {
                $default_charge += ($round_remaining * 50);
            }
        }

        // if (!$is_colombo) {
        //     $default_charge += 50;
        // }

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

                    $image_list = json_decode($value['images']);
                    
                    if ($value['images'] != null) {
                        $dataList[$key]['images'] = json_decode($value['images'])->image0;
                    } else {
                        $dataList[$key]['images'] = 'data:image/jpeg;base64,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';
                    }

                    $dataList[$key]['createTime'] = $value['create_time'];

                    if ($value['status'] == 1) {
                        $dataList[$key]['inStock'] = true;
                    } else {
                        $dataList[$key]['inStock'] = false;
                    }

                }
                
                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList, $AllCategory);
                
            } catch (\Exception $e) {
                dd($image_list != null);
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
