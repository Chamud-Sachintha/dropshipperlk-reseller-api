<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'reseller_id',
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

    public function get_log_by_seller($seller) {
        $map['reseller_id'] = $seller;

        return $this->where($map)->orderBy('create_time', 'desc')->get();
    }
}
