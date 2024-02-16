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
        'token',
        'login_time',
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

    public function update_login_token($uid, $tokenInfo) {
        $map['token'] = $tokenInfo['token'];
        $map['login_time'] = $tokenInfo['loginTime'];

        return $this->where(array('id' => $uid))->update($map);
    }

    public function find_by_ref_code($refCode) {
        $map['code'] = $refCode;

        return $this->where($map)->first();
    }

    public function get_team_by_ref_code($refCode) {
        $map['ref_code'] = $refCode;

        return $this->where($map)->get();
    }

    public function find_by_phone($phone) {
        $map['phone_number'] = $phone;

        return $this->where($map)->first();
    }

    public function find_by_token($token) {
        $map['token'] = $token;

        return $this->where($map)->first();
    }
}
