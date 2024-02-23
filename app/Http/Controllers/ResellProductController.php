<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderEn;
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
    private $Cart;
    private $CartItem;
    private $OrderEn;
    private $Order;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->Product = new Product();
        $this->ResellProduct = new ResellProduct();
        $this->Reseller = new Reseller();
        $this->Category = new Category();
        $this->Cart = new Cart();
        $this->CartItem = new CartItem();
        $this->OrderEn = new OrderEn();
        $this->Order = new Order();
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
                }

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function removeResellProduct(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $productId = (is_null($request->productId) || empty($request->productId)) ? "" : $request->productId;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($productId == "") {
            return $this->AppHelper->responseMessageHandle(0, "ProductId is required.");
        } else {
            
            try {
                $seller_info = $this->Reseller->find_by_token($request_token);

                $cart = $this->Cart->getCartBySeller($seller_info->id);
                $cart_items = $this->CartItem->validate_cart_item($productId, $cart->id);

                $order = $this->OrderEn->get_order_by_seller_ongoing($seller_info->id);

                if ($cart_items) {
                    return $this->AppHelper->responseMessageHandle(0, "This Product Added to the Cart");
                }

                foreach ($order as $key => $value) {
                    $order_items = $this->Order->get_items_by_pid_and_number($order->order, $productId);

                    if ($order_items) {
                        return $this->AppHelper->responseMessageHandle(0, "There is Order for This Product");
                    }
                }

                $remove_product = $this->ResellProduct->remove_product_by_seller_and_pid($seller_info->id, $productId);

                if ($remove_product) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
