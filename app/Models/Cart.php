<?php

namespace App\Models;

class Cart
{
    public $items = [];
    public $total_quantity = 0;
    public $total_price = 0;

    public function __construct($old_cart)
    {
        if ($old_cart) {
            $this->items = $old_cart->items;
            $this->total_quantity = $old_cart->total_quantity;
            $this->total_price = $old_cart->total_price;
        }
    }

    public function add($item, $id, $quantity)
    {
        $curr_item = null;

        foreach ($this->items as &$cart_item) {
            if ($cart_item["item"]->id === $id) {
                $curr_item = &$cart_item;
                break;
            }
        }

        if ($curr_item) {
            $curr_item["quantity"] += $quantity;
            $curr_item["price"] = $item->price * $curr_item["quantity"];
        } else {
            $curr_item = [
                "quantity" => $quantity,
                "price" => $item->price * $quantity,
                "item" => $item
            ];
            $this->items[] = $curr_item;
        }

        $this->total_quantity += $quantity;
        $this->total_price += ($item->price * $quantity);
    }


    public function delete($item, $id)
    {
        foreach ($this->items as $key => $curr_item) {
            if ($curr_item['item']['id'] === $item->id) {
                $this->total_quantity -= $curr_item['quantity'];
                $this->total_price -= $curr_item['price'];
                unset($this->items[$key]);
                $this->items = array_values($this->items);
                return true;
            }
        }
        return false;
    }
}
