<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcelOrderTmp extends Model
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
        'courier_name',
        'is_reseller_completed',
        'remark',
        'hold_notice',
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

    public function find_all() {
        return $this->all();
    }

    public function delete_by_id($order) {
        $map['id'] = $order[0]['id'];
        
        return $this->where($map)->delete();
    }
}
