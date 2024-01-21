<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderCancle extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'reseller',
        'total_amount',
        'status',               //0- pending 1- approve
        'create_time'
    ];

    public function add_log($info) {
        $map['order_id'] = $info['orderId'];
        $map['reseller'] = $info['reseller'];
        $map['total_amount'] = $info['totalAmount'];
        $map['status'] = $info['status'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }
}
