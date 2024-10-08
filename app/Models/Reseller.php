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

    public function get_team_by_ref_code_count($refCode) {
        $map['ref_code'] = $refCode;

        return $this->where($map)->count();
    }

    public function find_by_phone($phone) {
        $map['phone_number'] = $phone;

        return $this->where($map)->first();
    }

    public function find_by_token($token) {
        $map['token'] = $token;

        return $this->where($map)->first();
    }

    public function update_password($userId ,$userPass)
    {
        //$map['id'] = $userId;
        $map['password'] = $userPass;

        return $this->where(array('id' => $userId))->update($map);

    }

    public function update_by_token($sellerid,$profileData){
        $map['full_name'] = $profileData['fullName'];
        $map['b_name'] = $profileData['buisnessName'];
        $map['address'] = $profileData['address'];
        $map['email'] = $profileData['email'];

        return $this->where(array('id' => $sellerid))->update($map);

    }

    public function find_by_nic($nicNumber) {
        $map['nic_number'] = $nicNumber;

        return $this->where($map)->first();
    }

    public function get_pending_payout($seller) {
        $map['id'] = $seller;
    
        return $this->where($map)->pluck('profit_total')->first();
    }

    public function find_by_id($id) {
        $map['id'] = $id;

        return $this->where($map)->first();
    }
}
