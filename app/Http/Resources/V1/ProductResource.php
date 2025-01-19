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

        $isFavorite = false;
        $id = $this->id;
        $userId = Auth::user()->id;
        $exist = Favorite::where("user_id", $userId)->where("product_id", $id)->first();
        if (!empty($exist)) $isFavorite = true;

        //#################################################################################################
        //############################## Get the Product Image's URL ######################################

        if ($this->image_source === "")
            $photoUrl = "";
        else
            $photoUrl = Storage::url("public/" . $this->image_source);

        return [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "category" => $this->category,
            "price" => $this->price,
            "count" => $this->count,
            "imageURL" => $photoUrl,
            "isFavorite" => $isFavorite,
        ];
    }
}
