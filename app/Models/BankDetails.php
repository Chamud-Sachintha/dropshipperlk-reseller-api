<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'reselller_id',
        'bank_name',
        'account_number',
        'branch_code',
        'resellr_name',
        'create_time'
    ];

    public function find_id($id){
        $map['reselller_id'] = $id;

        return $this->where($map)->first();
    }

    public function Add_data($bankdata){
        $map['reselller_id'] = $bankdata['reselller_id'];
        $map['bank_name'] = $bankdata['bank_name'];
        $map['account_number'] = $bankdata['account_number'];
        $map['branch_code'] = $bankdata['branch_code'];
        $map['resellr_name'] = $bankdata['resellr_name'];
        $map['create_time'] = $bankdata['createTime'];

        return $this->create($map);
    }

    public function update_data($Bid,$bankdata){
       
        $map['bank_name'] = $bankdata['bank_name'];
        $map['account_number'] = $bankdata['account_number'];
        $map['branch_code'] = $bankdata['branch_code'];
        $map['resellr_name'] = $bankdata['resellr_name'];
        $map['create_time'] = $bankdata['createTime'];

        return $this->where(array('id' => $Bid))->update($map);

    }
}
