<?php

namespace app\models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_id'
    ];

    protected $casts = [
        'cart' => 'array'
    ];

    protected $appends = [
        'parsed_cart'
    ];

    public function getParsedCartAttribute () {
        $cart = $this->cart;
        $items = [];
        foreach($cart as $item) {
            $product = Product::where('id', $item['product_id'])->first();
            if($product) {
                $items[] =  [
                    'product' => $product,
                    'quantity' => $item['quantity']
                ];
            }

        }

        return $items;
    }

}