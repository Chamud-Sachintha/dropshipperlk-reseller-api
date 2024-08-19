<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'product_id',
        'order',                                // order number
        'name',
        'address',
        'city',
        'district',
        'contact_1',
        'contact_2',
        'quantity',
        'total_amount',
        'payment_method',
        'bank_slip',
        'payment_status',                       // 0- pending 1- paid  2- refund
        'order_status',                         // 0 - pending 1- hold 2- packaging 3- cancel 4- in courier 5- delivered
        'tracking_number',
        'is_reseller_completed',
        'create_time'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function orderEn()
    {
        return $this->hasOne(OrderEn::class, 'order', 'order');
    }

    public function add_log($orderInfo) {
        $map['reseller_id'] = $orderInfo['resellerId'];
        $map['product_id'] = $orderInfo['productId'];
        $map['order'] = $orderInfo['order'];
        $map['name'] = $orderInfo['name'];
        $map['address'] = $orderInfo['address'];
        $map['city'] = $orderInfo['city'];
        $map['district'] = $orderInfo['district'];
        $map['contact_1'] = $orderInfo['contact_1'];
        $map['contact_2'] = $orderInfo['contact_2'];
        $map['quantity'] = $orderInfo['quantity'];
        $map['total_amount'] = $orderInfo['totalAmount'];
        $map['payment_method'] = $orderInfo['paymentMethod'];
        $map['bank_slip'] = $orderInfo['bankSlip'];
        $map['payment_status'] = 0;
        $map['order_status'] = 0;
        $map['is_reseller_completed'] = $orderInfo['isResellerCompleted'];
        $map['create_time'] = $orderInfo['createTime'];

        return $this->create($map);
    }

    public function get_order_by_order_number($orderNumber) {
        $map['order'] = $orderNumber;

        return $this->where($map)->first();
    }

    public function get_order_by_order_number_new($orderNumber) {
        $map['order'] = $orderNumber;

        return $this->where($map)->get();
    }

    public function get_items_by_pid_and_number($oder, $pid) {
        $map['order'] = $oder;
        $map['product_id'] = $pid;

        return $this->where($map)->first();
    }

    public function get_order_items_by_order_number($orderNumber) {
        $map['order'] = $orderNumber;

        return $this->where($map)->get();
    }

    public function cancle_order_by_number($orderNumber) {
        $map['order'] = $orderNumber;
        $map1['order_status'] = 3;

        return $this->where($map)->update($map1);
    }

    public function find_by_id($oid) {
        $map['id'] = $oid;

        return $this->where($map)->first();
    }

    public function get_pending_count_by_seller($seller) {
        $map['reseller_id'] = $seller;
        $map['order_status'] = 0;

        return $this->where($map)->count();
    }

    public function get_in_courier_count_by_seller($seller) {
        $map['reseller_id'] = $seller;
        $map['order_status'] = 4;

        return $this->where($map)->count();
    }

    public function get_complete_count_by_seller($seller) {
        $map['reseller_id'] = $seller;
        $map['order_status'] = 5;

        return $this->where($map)->count();
    }

    public function get_camcle_count_by_seller($seller) {
        $map['reseller_id'] = $seller;
        $map['order_status'] = 3;

        return $this->where($map)->count();
    }

    public function get_paid_order_count($seller) {
        $map['reseller_id'] = $seller;
        $map['payment_status'] = 1;

        return $this->where($map)->count();
    }

    public function get_total_orders($seller) {
        $map['reseller_id'] = $seller;

        return $this->where($map)->count();
    }

    public function get_pending_payment($seller) {
        $map['reseller_id'] = $seller;
        $map['payment_status'] = 0;

        return $this->where($map)->sum("total_amount");
    }

    public function get_by_seller($seller) {
        $map['reseller_id'] = $seller;

        return $this->where($map)->get();
    }

    public function get_all() {
        return $this->all();
    }
}
