<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
        'order_id',
        'type',
        'product_id',
        'product_price',
        'resell_price',
        'quantity',
        'total_amount',
        'delivery_charge',
        'direct_commision',
        'team_commision',
        'profit',
        'profit_total',
        'create_time'
    ];

    public function get_team_com_by_seller($sellerId) {
        $map['reseller_id'] = $sellerId;

        return $this->where($map)->sum('team_commision');
    }

    public function get_direct_com_by_seller($sellerId) {
        $map['reseller_id'] = $sellerId;

        return $this->where($map)->sum('direct_commision');
    }

    public function get_log_by_seller($seller) {
        $map['reseller_id'] = $seller;

        return $this->where($map)->orderBy('id', 'desc')->get();
    }

    public function get_total_earnings($seller) {
        $map['reseller_id'] = $seller;
        
        return $this->where($map)->sum('profit_total');
    }
}
