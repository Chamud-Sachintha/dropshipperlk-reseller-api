<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderEn extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'order',
        'total_amount',
        'payment_method',
        'bank_slip',
        'payment_status',                       // 0- pending 1- paid  2- refund
        'order_status',                         // 0 - pending 1- hold 2- packaging 3- cancel 4- in courier 5- delivered
        'tracking_number',
        'is_reseller_completed',
        'create_time'
    ];

    public function add_log($orderInfo) {
        $map['reseller_id'] = $orderInfo['resellerId'];
        $map['order'] = $orderInfo['order'];
        $map['total_amount'] = $orderInfo['totalAmount'];
        $map['payment_method'] = $orderInfo['paymentMethod'];
        $map['bank_slip'] = $orderInfo['bankSlip'];
        $map['payment_status'] = 0;
        $map['order_status'] = 0;
        $map['is_reseller_completed'] = $orderInfo['isResellerCompleted'];
        $map['create_time'] = $orderInfo['createTime'];

        return $this->create($map);
    }

    public function get_order_by_seller($seller) {
        $map['reseller_id'] = $seller;

        return $this->where($map)->get();
    }

    public function getOrderInfoByOrderNumber($orderNumber) {
        $map['order'] = $orderNumber;

        return $this->where($map)->first();
    }

    public function get_order_by_seller_ongoing($seller) {
        $map['reseller_id'] = $seller;
        $map1['order_status'] = 5;

        return $this->where($map)->whereNotIn('order_status', [5])->get();

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
        $map['order_status'] = 7;

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
        $map['order_status'] = 5;

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

    public function get_pending_count($seller) {
        $map['reseller_id'] = $seller;
        $map['order_status'] = 5;

        return $this->where($map)->count();
    }
}
