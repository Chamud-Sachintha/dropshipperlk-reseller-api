<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\Reseller;
use App\Models\ResellProduct;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private $AppHelper;
    private $Product;
    private $Reseller;
    private $Cart;
    private $CartItem;
    private $ResellProduct;
    private $Category;

    public function __construct( )
    {
        $this->Product = new Product();
        $this->Reseller = new Reseller();
        $this->AppHelper = new AppHelper();
        $this->Cart = new Cart();
        $this->CartItem = new CartItem();
        $this->ResellProduct = new ResellProduct();
        $this->Category = new Category();
    }

    public function addCartProduct(Request $requerst) {

        $requerst_token = (is_null($requerst->token) || empty($requerst->token)) ? "" : $requerst->token;
        $productId = (is_null($requerst->productId) || empty($requerst->productId)) ? "" : $requerst->productId;
        $quantity = (is_null($requerst->quantity) || empty($requerst->quantity)) ? "" : $requerst->quantity;

        if ($requerst_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($productId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Product Id is required");
        } else if ($quantity == "") {
            return $this->AppHelper->responseMessageHandle(0, "Quantity is required.");
        } else {

            try {
                $seller_info = $this->Reseller->find_by_token($requerst_token);
                $validate_cart = $this->Cart->getCartBySeller($seller_info->id);

                $cart = null;
                if ($validate_cart == null) {
                    $cartInfo = array();
                    $cartInfo['sellerId'] = $seller_info['id'];
                    $cartInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $cart = $this->Cart->add_log($cartInfo);
                } else {
                    $cart = $validate_cart;
                }

                $validate_cart_item = $this->CartItem->validate_cart_item($productId, $cart->id);

                if ($validate_cart_item) {
                    return $this->AppHelper->responseMessageHandle(0, "Already Added to Cart");
                }

                $cart_items_insert = null;
                if ($cart != null) {

                    $resell_info = $this->ResellProduct->find_by_pid_and_sid($seller_info['id'], $productId);

                    $cartItemsArray = array();
                    $cartItemsArray['cartId'] = $cart->id;
                    $cartItemsArray['productId'] = $productId;
                    $cartItemsArray['quantity'] = $quantity;
                    $cartItemsArray['total'] = $resell_info['price'] * $quantity;
                    $cartItemsArray['createTime'] = $this->AppHelper->get_date_and_time();

                    $cart_items_insert = $this->CartItem->add_log($cartItemsArray);
                }

                if ($cart_items_insert) {
                    $cart_total_info['id'] = $cart->id;
                    $cart_total_info['cartTotal'] = $cart->cart_total + ($resell_info['price'] * $quantity);

                    $this->Cart->update_cart_total($cart_total_info);

                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else {
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getCartItemsCount(Request $requerst) {

        $requerst_token = (is_null($requerst->token) || empty($requerst->token)) ? "" : $requerst->token;

        if ($requerst_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $reseller = $this->Reseller->find_by_token($requerst_token);
                $cart_info = $this->Cart->getCartBySeller($reseller->id);
                $cart_items = $this->CartItem->get_cart_item_count($cart_info['id']);

                $dataList['cartItemsCount'] = $cart_items;

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function getCartItems(Request $requerst) {

        $requerst_token = (is_null($requerst->token) || empty($requerst->token)) ? "" : $requerst->token;

        if ($requerst_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Toekn is required.");
        } else {

            try {
                $selelr_info = $this->Reseller->find_by_token($requerst_token);
                $cart_info = $this->Cart->getCartBySeller($selelr_info->id);
                // $resp = $this->Cart->getAllCartItemsBySeller($selelr_info->id);
                $resp = $this->CartItem->getAllCartItemsBySeller($cart_info->id);

                $dataList = array();
                foreach ($resp as $key => $value) {

                    $file_server_path = $this->AppHelper->getRootPath();

                    $product_info = $this->Product->find_by_id($value['product_id']);
                    $resell_info = $this->ResellProduct->find_by_pid_and_sid($selelr_info->id, $value['product_id']);
                    $category_info = $this->Category->find_by_id($product_info['category']);
                    
                    $dataList[$key]['productId'] = $value['product_id'];
                    $dataList[$key]['productName'] = $product_info['product_name'];
                    $dataList[$key]['image'] = $file_server_path . "images/" . json_decode($product_info['images'])->image0;
                    $dataList[$key]['price'] = $value['total'];
                    $dataList[$key]['categoryName'] = $category_info['category_name'];
                    $dataList[$key]['createTime'] = $value['create_time'];
                }

                $dataList['totalAmount'] = $cart_info['cart_total'];

                return $this->AppHelper->responseEntityHandle(1, "Operation Complete", $dataList);
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function placeCartItemsOrder(Request $requerst) {

        $requerst_token = (is_null($requerst->token) || empty($requerst->token)) ? "" : $requerst->token;

        if ($requerst_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $reseller_info = $this->Reseller->find_by_token($requerst_token);

                
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }

    public function removeCartItemById(Request $requerst) {

        $requerst_token = (is_null($requerst->token) || empty($requerst->tokoen)) ? "" : $requerst->token;
        $id = (is_null($requerst->id) || empty($requerst->id)) ? "" : $requerst->id;

        if ($requerst_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($id == "") {
            return $this->AppHelper->responseMessageHandle(0, "Product Id is required");
        } else {

            try {
                $resp = $this->Cart->remove_product_from_cart_by_id($id); 

                if ($resp) {
                    return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                } else { 
                    return $this->AppHelper->responseMessageHandle(0, "Error Occured");
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
