<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProductCollection;
use App\Http\Resources\V1\ProductResource;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function addFavorite(string $id)
    {
        $user_id = Auth::user()->id;
        $exist = Favorite::where("user_id", $user_id)->where("product_id", $id)->first();
        if (!empty($exist)) {
            $exist->delete();
            return response([
                "message" => "Deleted Successfully 🚫",
            ], 200);
        }

        Favorite::create([
            "user_id" => $user_id,
            "product_id" => $id,
        ]);

        return response([
            "message" => "Added Successfully 🔥",
        ], 201);
    }

    public function getFavorite()
    {
        $user = Auth::user();
        $favoriteProducts = $user->favorites()->with("product")->get()->map(function ($favorite) {
            return $favorite->product;
        });

        return new ProductCollection($favoriteProducts);
    }

    public function productIsFavorite(string $id)
    {
        $user_id = Auth::user()->id;
        $exist = Favorite::where("user_id", $user_id)->where("product_id", $id)->first();

        if (empty($exist)) return response(["Answer" => false], 200);
        else return response(["Answer" => true],200);
    }
}
