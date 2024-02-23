<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderEn;
use App\Models\Reseller;
use Illuminate\Http\Request;

class OrderEnController extends Controller
{
    private $AppHelper;
    private $OrderEn;
    private $Reseller;
    private $Cart;
    private $CartItem;
    private $Order;

    public function __construct()
    {
        $this->AppHelper = new AppHelper();
        $this->OrderEn = new OrderEn();
        $this->Reseller = new Reseller();
        $this->Cart = new Cart();
        $this->CartItem = new CartItem();
        $this->Order = new Order();
    }

    public function placeNewOrder(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $paymentMethod = (is_null($request->paymentMethod) || empty($request->paymentMethod)) ? "" : $request->paymentMethod;
        $bankSlip = (is_null($request->bankSlip) || empty($request->bankSlip)) ? "" : $request->bankSlip;

        // customer details for order

        $name = (is_null($request->name) || empty($request->name)) ? "" : $request->name;
        $address = (is_null($request->address) || empty($request->address)) ? "" : $request->address;
        $city = (is_null($request->city) || empty($request->city)) ? "" : $request->city;
        $district = (is_null($request->district) || empty($request->district)) ? "" : $request->district;
        $f_contact = (is_null($request->firstContact) || empty($request->firstContact)) ? "" : $request->firstContact;
        $s_contact = (is_null($request->secondContact) || empty($request->secondContact)) ? "" : $request->secondContact;
        
        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else {

            try {
                $seller_info = $this->Reseller->find_by_token($request_token);
                $cart_info = $this->Cart->getCartBySeller($seller_info['id']);

                $orderInfo = array();

                if ($cart_info) {

                    $order_number = $this->AppHelper->generate_ref(10);

                    $orderInfo['resellerId'] = $seller_info['id'];
                    $orderInfo['order'] = $order_number;
                    $orderInfo['totalAmount'] = $cart_info['cart_total'];
                    $orderInfo['paymentMethod'] = $paymentMethod;
                   
                    if ($bankSlip != "") {
                        $orderInfo['bankSlip'] = $this->AppHelper->decodeImage($bankSlip);
                    } else {
                        $orderInfo['bankSlip'] = null;
                    }

                    $orderInfo['isResellerCompleted'] = 0;
                    $orderInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $create_order = $this->OrderEn->add_log($orderInfo);

                    if ($create_order) {
                        $cart_items_list = $this->CartItem->getAllCartItemsBySeller($cart_info['id']);

                        foreach ($cart_items_list as $key => $value) {
                            $orderInfo = array();
                            $orderInfo['productId'] = $value['product_id'];
                            $orderInfo['resellerId'] = $seller_info->id;
                            $orderInfo['order'] = $order_number;
                            $orderInfo['name'] = $name;
                            $orderInfo['address'] = $address;
                            $orderInfo['city'] = $city;
                            $orderInfo['district'] = $district;
                            $orderInfo['contact_1'] = $f_contact;
                            $orderInfo['contact_2'] = $s_contact;
                            $orderInfo['quantity'] = $value['quantity'];
                            $orderInfo['totalAmount'] = $value['total'];
                            $orderInfo['paymentMethod'] = $paymentMethod;
                            
                            if ($bankSlip != "") {
                                $orderInfo['bankSlip'] = $this->AppHelper->decodeImage($bankSlip);
                            } else {
                                $orderInfo['bankSlip'] = null;
                            }
                            
                            $orderInfo['isResellerCompleted'] = 0;
                            $orderInfo['createTime'] = $this->AppHelper->get_date_and_time();

                            $this->Order->add_log($orderInfo);
                        }

                        $remove_cart_items = $this->CartItem->delete_cart_items($cart_info['id']);
                        $remove_cart = $this->Cart->delete_cart($cart_info['id']);

                        if ($remove_cart_items && $remove_cart) {
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

    private function validate_order($seller) {
        $resp = $this->OrderEn->get_order_by_seller($seller);

        $order = null;

        if ($resp) {
            $order = $resp;
        } else {
            
        }

        return $order;
    }
}
