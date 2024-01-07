<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResellProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'product_id',
        'price',
        'status',
        'create_time'
    ];

    public function add_log($resellInfo) {
        $map['reseller_id'] = $resellInfo['resellerId'];
        $map['product_id'] = $resellInfo['productId'];
        $map['price'] = $resellInfo['price'];
        $map['status'] = 1;
        $map['create_time'] = $resellInfo['createTime'];

        return $this->create($map);
    }

    public function find_by_pid_and_sid($sid, $pid) {
        $map['reseller_id'] = $sid;
        $map['product_id'] = $pid;

        return $this->where($map)->first();
    }

    public function get_all($sid) {
        $map['reseller_id'] = $sid;

        return $this->where($map)->get();
    }
}
