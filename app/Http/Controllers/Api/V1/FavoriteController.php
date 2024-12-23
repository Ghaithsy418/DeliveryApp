<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductCollection;
use App\Http\Resources\V1\ProductResource;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function addFavorite(string $id)
    {
        $user_id = Auth::user()->id;
        $product = Product::find($id);

        if(!$product) return response([
            "message" => "there is no product with this id",
        ],404);

        $exist = Favorite::where("user_id", $user_id)->where("product_id", $id)->first();
        if (!empty($exist)) {
            $exist->delete();
            return response([
                "message" => "Deleted Successfully 🚫",
                "current" => false,
            ], 200);
        }

        Favorite::create([
            "user_id" => $user_id,
            "product_id" => $id,
        ]);

        return response([
            "message" => "Added Successfully 🔥",
            "current" => true,
        ], 201);
    }

    public function getFavorite()
    {
        $user_id = Auth::user()->id;
        $user = User::find($user_id);

        $favoriteProducts = $user->favorites()->with("product")->get()->map(function ($favorite) {
            return $favorite->product;
        });

        return new ProductCollection($favoriteProducts);
    }
}
