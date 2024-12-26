<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\V1\StoreProductRequest;
use App\Http\Requests\V1\UpdateProductRequest;
use App\Http\Resources\V1\ProductCollection;
use App\Http\Resources\V1\ProductResource;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProductController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new ProductCollection(Product::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        if ($request->hasFile("imageSource")){
            $path = $request->file("imageSource")->store("products-images","public");
            return new ProductResource(Product::create([
                "name" => $request->name,
                "description" => $request->description,
                "category" => $request->category,
                "price" => $request->price,
                "count" => $request->count,
                "store_id" => $request->storeId,
                "sold_count" => $request->soldCount,
                "image_source" => $path,
            ]));
        }
        else{
            return new ProductResource(Product::create($request->all()));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, string $id)
    {
        $product = Product::find($id);
        $product->update($request->all());
        return new ProductResource($product);
    }

    public function destroy(string $id)
    {
        $product = Product::find($id);
        $product->delete();

        return response([
            "message" => "the product that has the id $product->id has been deleted successfully",
        ], 200);
    }

    public function AddToCart(Request $request, string $id)
    {

        $quantity = $request->validate([
            "quantity" => "required",
        ]);

        $product = Product::find($id);

        if(!$product) return response(["message" => "No Product with this id"],404);

        $curr_user_id = Auth::user()->id;
        $old_cart = Session::has("cart" . (string)$curr_user_id) ? Session::get("cart" . (string)$curr_user_id) : null;
        $cart = new Cart($old_cart);
        $cart->add($product, $product->id, $quantity["quantity"]);

        Session::put("cart" . (string)$curr_user_id, $cart);

        return response([
            "message" => "Added Successfully",
            "cart" => $cart,
        ], 201);
    }

    public function AddAllToCart(){
        $user_id = Auth::user()->id;
        $user = User::find($user_id);

        $favoriteProducts = $user->favorites()->with("product")->get()->map(function ($favorite) {
            return $favorite->product;
        });

        $old_cart = Session::has("cart" . (string)$user_id) ? Session::get("cart" . (string)$user_id) : null;
        $cart = new Cart($old_cart);

        foreach($favoriteProducts as $product){
            $cart->add($product,$product->id,1);
        }
        Session::put("cart" . (string)$user_id, $cart);

        return [$cart];

    }

    public function GetCart()
    {
        $currUserId = Auth::user()->id;
        return response([
            "cart" => Session::get("cart" . (string)$currUserId),
        ], 200);
    }

    public function DeleteCartProduct(Request $request, string $id)
    {

        $currUserId = Auth::user()->id;
        $product = Product::find($id);

        if(!$product) return response(["message" => "No Product with this id"],404);

        $oldCart = Session::get("cart" . (string)$currUserId);
        $cart = new Cart($oldCart);

        $bool = $cart->delete($product, $id);

        Session::put("cart" . (string)$currUserId, $cart);

        if (!$bool) return response([
            "message" => "Nothing to delete here",
        ], 404);

        return response([
            "message" => "Deleted Successfully",
            "new cart" => Session::get("cart" . (string)$currUserId),
        ], 200);
    }

    public function purchase(Request $request)
    {

        $user_id = Auth::user()->id;

        foreach ($request->all() as $product) {
            $product_id = $product["id"];
            $quantity = $product["quantity"];

            $the_product = Product::find($product_id);

            if($the_product->count < $quantity) return response(["message" => "not enough quantity"], 404);


            $the_product->update([
                "count" => $the_product->count - $quantity,
                "sold_count" => $the_product->sold_count + $quantity,
            ]);

            $column = DB::table("users_products_pivot")->where("user_id", $user_id)->where("product_id", $product_id);
            $exist = $column->first();

            if (!empty($exist)) {
                $column->update([
                    "quantity" => $exist->quantity + $quantity,
                ]);
            } else {
                DB::table("users_products_pivot")->insert([
                    "user_id" => $user_id,
                    "product_id" => $product_id,
                    "quantity" => $quantity,
                ]);
            }
        }

        Session::forget("cart" . (string)$user_id);
        return response(["message"=>"Done Successfully"],200);
    }

    public function categories(string $category)
    {
        $product = Product::where("category", $category)->first();

        if(empty($product)) return response(["message" => "didn't find any product"],404);

        return new ProductResource($product);
    }
}
