<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\V1\StoreProductRequest;
use App\Http\Requests\V1\UpdateProductRequest;
use App\Http\Resources\V1\ProductCollection;
use App\Http\Resources\V1\ProductResource;
use App\Models\Cart;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class ProductController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new ProductCollection(Product::paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        return new ProductResource(Product::create($request->all()));
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
        $currUserId = Auth::user()->id;
        $oldCart = Session::has("cart" . (string)$currUserId) ? Session::get("cart" . (string)$currUserId) : null;
        $cart = new Cart($oldCart);
        $cart->add($product, $product->id, $quantity["quantity"]);

        Session::put("cart" . (string)$currUserId, $cart);

        return response([
            "message" => "Added Successfully",
            "cart" => $cart,
        ], 200);
    }

    public function GetCart()
    {
        $currUserId = Auth::user()->id;
        return response([
            "Cart" => Session::get("cart" . (string)$currUserId),
        ], 200);
    }

    public function DeleteCartProduct(Request $request, string $id)
    {

        $quantity = $request->validate([
            "quantity" => "required",
        ]);

        $currUserId = Auth::user()->id;
        $product = Product::find($id);
        $oldCart = Session::get("cart" . (string)$currUserId);
        $cart = new Cart($oldCart);

        $bool = $cart->delete($product, $id, $quantity["quantity"]);

        Session::put("cart" . (string)$currUserId, $cart);

        if (!$bool) return response([
            "message" => "Bad Request (Nothing to delete here or Problem with the Quantity)",
        ], 400);

        return response([
            "message" => "Deleted Successfully",
            "new cart" => Session::get("cart" . (string)$currUserId),
        ], 200);
    }
}
