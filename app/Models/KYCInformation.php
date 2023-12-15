<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KYCInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'front_image_nic',
        'back_image_nic',
        'status',
        'create_time',
        'mod_time'
    ];

    public function add_log($kycInfo) {
        $map['client_id'] = $kycInfo['clientId'];
        $map['front_image_nic'] = $kycInfo['frontImg'];
        $map['back_image_nic'] = $kycInfo['backImg'];
        $map['status'] = 0;
        $map['create_time'] = $kycInfo['createTime'];
        $map['mod_time'] = $kycInfo['modTime'];

        return $this->create($map);
    }

    public function get_kyc_by_uid($uid) {
        $map['client_id'] = $uid;

        return $this->where($map)->first();
    }
}
