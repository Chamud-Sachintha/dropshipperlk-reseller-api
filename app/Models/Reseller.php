<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reseller extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'b_name',
        'address',
        'phone_number',
        'nic_number',
        'email',
        'password',
        'ref_code',
        'code',
        'create_time'
    ];

    public function add_log($sellerInfo) {
        $map['full_name'] = $sellerInfo['fullName'];
        $map['b_name'] = $sellerInfo['bName'];
        $map['address'] = $sellerInfo['address'];
        $map['phone_number'] = $sellerInfo['phoneNumber'];
        $map['nic_number'] = $sellerInfo['nicNumber'];
        $map['email'] = $sellerInfo['email'];
        $map['password'] = $sellerInfo['password'];
        $map['ref_code'] = $sellerInfo['refCode'];
        $map['code'] = $sellerInfo['code'];
        $map['create_time'] = $sellerInfo['createTime'];

        return $this->create($map);
    }

    public function validate_ref_code($refCode) {
        $map['code'] = $refCode;

        return $this->where($map)->first();
    }

    public function get_count_by_ref_code($refCode) {
        $map['ref_code'] = $refCode;

        return $this->where($map)->count();
    }
}
