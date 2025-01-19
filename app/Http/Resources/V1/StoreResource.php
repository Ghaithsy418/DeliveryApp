<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        //##################### Get The Photos URLs ###########################
        if(!$this->image_source){
            $photo_url = "";
        }else{
            $photo_url = Storage::url("public/" . $this->image_source);
        }

        //#####################################################################

        return [
            "id" => $this->id,
            "name" => $this->name,
            "type" => $this->type,
            "description" => $this->description,
            "location" => $this->location,
            "photoURL" => $photo_url,
            "products" => ProductResource::collection($this->whenLoaded("products")),
        ];
    }
}
