<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InCourierDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'order',
        'way_bill',
        'package_create_status',
        'create_time',
    ];

    public function find_by_order($order) {
        $map['order'] = $order;

        return $this->where($map)->first();
    }
}
