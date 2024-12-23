<?php

namespace App\Http\Resources\V1;

use App\Models\Favorite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        //#################### Check if the Product is from a User Favorite List #########################

        $is_favorite = false;
        $id = $this->id;
        $user_id = Auth::user()->id;
        $exist = Favorite::where("user_id", $user_id)->where("product_id", $id)->first();
        if (!empty($exist)) $is_favorite = true;

        //#################################################################################################
        //############################## Get the Product Image's URL ######################################

        if ($this->image_source === "")
            $photo_url = "";
        else
            $photo_url = Storage::url("public/" . $this->image_source);


        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "category" => $this->category,
            "price" => $this->price,
            "count" => $this->count,
            "imageURL" => $photo_url,
            "isFavorite" => $is_favorite,
        ];
    }
}
