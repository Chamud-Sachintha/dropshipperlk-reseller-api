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

    public function get_all() {
        return $this->all();
    }
}
