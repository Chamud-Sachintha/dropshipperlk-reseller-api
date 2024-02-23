<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'cart_total',
        'create_time'
    ];

    public function add_log($info) {
        $map['seller_id'] = $info['sellerId'];
        // $map['product_id'] = $info['productId'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function update_cart_total($info) {
        $map['id'] = $info['id'];
        $map1['cart_total'] = $info['cartTotal'];

        return $this->where($map)->update($map1);
    }

    public function getCartBySeller($seller) {
        $map['seller_id'] = $seller;

        return $this->where($map)->first();
    }    

    public function delete_cart($id) {
        $map['id'] = $id;

        return $this->where($map)->delete();
    }

    public function remove_product_from_cart_by_id($id) {
        $map['id'] = $id;

        return $this->where($map)->delete();
    }

    
}
