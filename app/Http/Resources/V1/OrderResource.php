<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            "userId" => $this->user_id,
            "totalPrice" => $this->total_price,
            "status" => $this->status,
            "createdAt" => $this->created_at,
            "updatedAt" => $this->updated_at,
            "orderItems" => new OrderItemCollection($this->orderItems),
        ];
    }
}
