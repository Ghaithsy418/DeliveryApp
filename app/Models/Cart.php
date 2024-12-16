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

    public function add($item, $id,$quantity)
    {

        $currItem = ["quantity" => 0, "price" => $item->price, "item" => $item];
        if (array_key_exists($id, $this->items)) {
            $currItem = $this->items[$id];
        }
        $currItem["quantity"] += $quantity;
        $currItem["price"] = $item->price * $currItem["quantity"];
        $this->items[$id] = $currItem;
        $this->totalQuantity += $quantity;
        $this->totalPrice += ($item->price * $quantity);
    }

    public function delete($item, $id,$quantity)
    {
        if (!array_key_exists($id, $this->items)) {
            return false;
        }

        if($this->items[$id]["quantity"] < $quantity) return false;

        $this->items[$id]["quantity"] -= $quantity;
        $this->items[$id]["price"] -= ($item->price * $quantity);
        if($this->items[$id]["quantity"] === 0){
            unset($this->items[$id]);
        }
        $this->totalQuantity -= $quantity;
        $this->totalPrice -= ($item->price * $quantity);

        return true;
    }
}
