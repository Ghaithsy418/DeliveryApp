<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "orderId" => $this->order_id,
            "productId" => $this->product_id,
            "quantity" => $this-> quantity,
            "price" => $this->price,
            "product" => new ProductResource($this->product),
        ];
    }
}
