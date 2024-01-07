<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'price',
        'category',
        'team_commision',
        'direct_commision',
        'is_store_pick',
        'waranty',
        'description',
        'supplier_name',
        'stock_count',
        'images',
        'create_time'
    ];

    public function find_by_id($pid) {
        $map['id'] = $pid;

        return $this->where($map)->first();
    }

    public function get_all_products() {
        return $this->all();
    }
}
