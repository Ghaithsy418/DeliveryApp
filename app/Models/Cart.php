<?php

namespace App\Models;

class Cart
{
    public $items = [];
    public $totalQuantity = 0;
    public $totalPrice = 0;

    public function __construct($oldCart)
    {
        if ($oldCart) {
            $this->items = $oldCart->items;
            $this->totalQuantity = $oldCart->totalQuantity;
            $this->totalPrice = $oldCart->totalPrice;
        }
    }

    public function add($item, $id, $quantity)
    {
        $currItem = null;

        foreach ($this->items as &$cartItem) {
            if ($cartItem["item"]->id === $id) {
                $currItem = &$cartItem;
                break;
            }
        }

        if ($currItem) {
            $currItem["quantity"] += (int)$quantity;
            $currItem["price"] = (int)($item->price * $currItem["quantity"]);
        } else {
            $currItem = [
                "quantity" => (int)$quantity,
                "price" => (int)($item->price * $quantity),
                "item" => $item
            ];
            $this->items[] = $currItem;
        }

        $this->totalQuantity += (int)$quantity;
        $this->totalPrice += (int)($item->price * $quantity);
    }


    public function delete($item, $id)
    {
        foreach ($this->items as $key => $currItem) {
            if ($currItem['item']['id'] === $item->id) {
                $this->totalQuantity -= $currItem['quantity'];
                $this->totalPrice -= $currItem['price'];
                unset($this->items[$key]);
                $this->items = array_values($this->items);
                return true;
            }
        }
        return false;
    }
}
