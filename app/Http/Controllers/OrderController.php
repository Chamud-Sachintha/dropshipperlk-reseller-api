<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Order;
use App\Models\Product;
use App\Models\Reseller;
use App\Models\ResellProduct;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    private $Reseller;
    private $AppHelper;
    private $Order;
    private $Product;
    private $ResellProduct;

    public function __construct()
    {
        $this->Reseller = new Reseller();
        $this->AppHelper = new AppHelper();
        $this->Order = new Order();
        $this->Product = new Product();
        $this->ResellProduct = new ResellProduct();
    }

    public function placeNewOrderRequest(Request $request) {

        $request_token = (is_null($request->token) || empty($request->token)) ? "" : $request->token;
        $productId = (is_null($request->pid) || empty($request->pid)) ? "" : $request->pid;
        $name = (is_null($request->name) || empty($request->name)) ? "" : $request->name;
        $address = (is_null($request->address) || empty($request->address)) ? "" : $request->address;
        $city = (is_null($request->city) || empty($request->city)) ? "" : $request->city;
        $district = (is_null($request->district) || empty($request->district)) ? "" : $request->district;
        $f_contact = (is_null($request->firstContact) || empty($request->firstContact)) ? "" : $request->firstContact;
        $s_contact = (is_null($request->secondContact) || empty($request->secondContact)) ? "" : $request->secondContact;
        $paymentMethod = (is_null($request->paymentMethod) || empty($request->paymentMethod)) ? "" : $request->paymentMethod;
        $quantity = (is_null($request->quantity) || empty($request->quantity)) ? "" : $request->quantity;

        if ($request_token == "") {
            return $this->AppHelper->responseMessageHandle(0, "Token is required.");
        } else if ($productId == "") {
            return $this->AppHelper->responseMessageHandle(0, "Product ID is required.");
        } else if ($name == "") {
            return $this->AppHelper->responseMessageHandle(0, "Name is required.");
        } else if ($address == "") {
            return $this->AppHelper->responseMessageHandle(0, "Address is required.");
        } else if ($city == "") {
            return $this->AppHelper->responseMessageHandle(0, "City is required.");
        } else if ($district == "") {
            return $this->AppHelper->responseMessageHandle(0, "District is required.");
        } else if ($f_contact == "") {
            return $this->AppHelper->responseMessageHandle(0, "Contact 1 is required.");
        } else if ($s_contact == "") {
            return $this->AppHelper->responseMessageHandle(0, "Contact 2 is required.");
        } else if ($paymentMethod == "") {
            return $this->AppHelper->responseMessageHandle(0, "Payment Method is required.");
        } else {

            try {
                $reseller = $this->Reseller->find_by_token($request_token);
                $product = $this->Product->find_by_id($productId);
                $resell_product = $this->ResellProduct->find_by_pid_and_sid($reseller->id, $productId);

                if ($product) {
                    $orderInfo = array();
                    $orderInfo['productId'] = $productId;
                    $orderInfo['resellerId'] = $reseller->id;
                    $orderInfo['order'] = $this->AppHelper->generate_ref(10);
                    $orderInfo['name'] = $name;
                    $orderInfo['address'] = $address;
                    $orderInfo['city'] = $city;
                    $orderInfo['district'] = $district;
                    $orderInfo['contact_1'] = $f_contact;
                    $orderInfo['contact_2'] = $s_contact;
                    $orderInfo['quantity'] = $quantity;
                    $orderInfo['totalAmount'] = $resell_product['price'] * $quantity;
                    $orderInfo['paymentMethod'] = $paymentMethod;
                    $orderInfo['isResellerCompleted'] = 0;
                    $orderInfo['createTime'] = $this->AppHelper->get_date_and_time();

                    $order = $this->Order->add_log($orderInfo);

                    if ($order) {
                        return $this->AppHelper->responseMessageHandle(1, "Operation Complete");
                    } else {
                        return $this->AppHelper->responseMessageHandle(0, "Error Occured.");
                    }
                }
            } catch (\Exception $e) {
                return $this->AppHelper->responseMessageHandle(0, $e->getMessage());
            }
        }
    }
}
