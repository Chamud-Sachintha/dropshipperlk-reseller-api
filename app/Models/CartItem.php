<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'total',
        'create_time'
    ];

    public function add_log($info) {
        $map['cart_id'] = $info['cartId'];
        $map['product_id'] = $info['productId'];
        $map['quantity'] = $info['quantity'];
        $map['total'] = $info['total'];
        $map['create_time'] = $info['createTime'];

        return $this->create($map);
    }

    public function getAllCartItemsBySeller($cartId) {
        $map['cart_id'] = $cartId;

        return $this->where($map)->get();
    }

    public function delete_cart_items($cartId) {
        $map['cart_id'] = $cartId;

        return $this->where($map)->delete();
    }

    public function validate_cart_item($pid, $cid) {
        $map['cart_id'] = $cid;
        $map['product_id'] = $pid;

        return $this->where($map)->first();
    }

    public function get_cart_item_count($cartId) {
        $map['cart_id'] = $cartId;

        return $this->where($map)->count();
    }

   

    public function remove_product_from_cart_by_id($id) {
        $map['id'] = $id;

        return $this->where($map)->delete();
    }
}
