<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Http\Requests\V1\StoreProductRequest;
use App\Http\Requests\V1\UpdateProductRequest;
use App\Http\Resources\V1\OrderCollection;
use App\Http\Resources\V1\ProductCollection;
use App\Http\Resources\V1\ProductResource;
use App\Jobs\FulfillOrder;
use App\Models\Cart;
use App\Models\Favorite;
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
        if ($request->hasFile("imageSource")) {
            $path = $request->file("imageSource")->store("products-images", "public");
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
        } else {
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

        if (!$product) return response(["message" => "No Product with this id"], 404);

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

    public function AddAllToCart()
    {
        $user_id = Auth::user()->id;
        $user = User::find($user_id);

        $favoriteProducts = $user->favorites()->with("product")->get()->map(function ($favorite) {
            return $favorite->product;
        });

        $old_cart = Session::has("cart" . (string)$user_id) ? Session::get("cart" . (string)$user_id) : null;
        $cart = new Cart($old_cart);

        foreach ($favoriteProducts as $product) {
            $cart->add($product, $product->id, 1);
        }
        Session::put("cart" . (string)$user_id, $cart);

        return response(["message"=>"good"],200);
    }

    public function GetCart()
    {
        $currUserId = Auth::user()->id;
        return response([
            "cart" => Session::get("cart" . (string)$currUserId),
        ], 200);
    }

    public function DeleteCartProduct(string $id)
    {

        $currUserId = Auth::user()->id;
        $product = Product::find($id);

        if (!$product) return response(["message" => "No Product with this id"], 404);

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
        if (!$request->all()) return response(["message" => "Nothing to add here"], 404);
        $user_id = Auth::user()->id;
        $old_cart = null;
        $cart = new Cart($old_cart);
        foreach ($request->all() as $product) {
            $product_id = $product["id"];
            $quantity = $product["quantity"];

            $the_product = Product::find($product_id);

            $cart->add($the_product, $product_id, $quantity);
        }
        DB::beginTransaction();

        try {
            $order = new Order();
            $order->user_id = $user_id;
            $order->total_price = $cart->total_price;
            $order->status = "pending";
            $order->save();

            foreach ($cart->items as $item) {
                $orderItem = new OrderItem();
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $item["item"]->id;
                $orderItem->quantity = $item["quantity"];
                $orderItem->price = $item["price"];
                $orderItem->save();

                $product = Product::find($item["item"]->id);
                if ($product->count < $item["quantity"]) {
                    DB::rollBack();
                    return response(["message" => "Not enough quantity for product Name: " . $product->name], 404);
                }
                $product->update([
                    "count" => $product->count - $item["quantity"],
                    "sold_count" => $product->sold_count + $item["quantity"],
                ]);
            }
            Session::forget("cart" . (string)$user_id);
            DB::commit();

            FulfillOrder::dispatch($order->id)->delay(now()->addMinutes(1));

            return response(["message" => "Order placed successfully"], 200);
        } catch (\Exception $err) {
            DB::rollBack();
            return response(["message" => "Something went wrong, plz try again!", "error" => $err->getMessage()], 500);
        }
    }


    public function getOrders()
    {
        $user_id = Auth::user()->id;
        $user = User::find($user_id);

        $data = new OrderCollection($user->orders()->with("orderItems.product")->get());
        return response($data, 200);
    }

    public function categories(string $category)
    {
        $product = Product::where("category", $category)->get();

        if (empty($product)) return response(["message" => "didn't find any product"], 404);

        return new ProductCollection($product);
    }

    public function getInvoice(Request $request)
    {

        if(!$request->all()) return response(["message" => "nothing to give you"],404);

        $products = [];
        $totalPrice = 0;

        foreach ($request->all() as $item) {
            $product = Product::find($item["id"]);
            $info = ["name" => $product->name, "price" => $product->price * $item["quantity"], "quantity" => $item["quantity"]];
            array_push($products, $info);
            $totalPrice += $product->price * $item["quantity"];
        }

        array_push($products, ["totalPrice" => $totalPrice]);

        return response([$products], 200);
    }
}
