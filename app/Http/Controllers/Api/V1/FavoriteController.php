<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductCollection;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function addFavorite(string $id)
    {
        $userId = Auth::user()->id;
        $product = Product::find($id);

        if(!$product) return response([
            "message" => "there is no product with this id",
        ],404);

        $exist = Favorite::where("user_id", $userId)->where("product_id", $id)->first();
        if (!empty($exist)) {
            $exist->delete();
            return response([
                "message" => "Deleted Successfully 🚫",
                "current" => false,
            ], 200);
        }

        Favorite::create([
            "user_id" => $userId,
            "product_id" => $id,
        ]);

        return response([
            "message" => "Added Successfully 🔥",
            "current" => true,
        ], 201);
    }

    public function getFavorite()
    {
        $userId = Auth::user()->id;
        $user = User::find($userId);

        $favoriteProducts = $user->favorites()->with("product")->get()->map(function ($favorite) {
            return $favorite->product;
        });

        return new ProductCollection($favoriteProducts);
    }
}
