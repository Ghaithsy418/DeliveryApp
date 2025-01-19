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
use App\Jobs\OrderNotification;
use App\Models\Cart;
use App\Models\User;
use App\Services\FCMService;
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
        $product = null;
        if ($request->hasFile("imageSource")) {
            $path = $request->file("imageSource")->store("products-images", "public");
            $product = new ProductResource(Product::create([
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
            $product = new ProductResource(Product::create($request->all()));
        }
        $fcmService = new FCMService();
        $user = Auth::user();
        $fcmService->notifyUsers("Product has been Added", (string)"the Admin " . $user->first_name . " has added " . $product->name);
        return response([$product], 201);
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

        $currUserId = Auth::user()->id;
        $oldCart = Session::has("cart" . (string)$currUserId) ? Session::get("cart" . (string)$currUserId) : null;
        $cart = new Cart($oldCart);
        $cart->add($product, $product->id, $quantity["quantity"]);

        Session::put("cart" . (string)$currUserId, $cart);

        return response([
            "message" => "Added Successfully",
            "cart" => $cart,
        ], 201);
    }

    public function AddAllToCart()
    {
        $userId = Auth::user()->id;
        $user = User::find($userId);

        $favoriteProducts = $user->favorites()->with("product")->get()->map(function ($favorite) {
            return $favorite->product;
        });

        $oldCart = Session::has("cart" . (string)$userId) ? Session::get("cart" . (string)$userId) : null;
        $cart = new Cart($oldCart);

        foreach ($favoriteProducts as $product) {
            $cart->add($product, $product->id, 1);
        }
        Session::put("cart" . (string)$userId, $cart);

        return response(["message" => "good"], 200);
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
        $userId = Auth::user()->id;
        $oldCart = null;
        $cart = new Cart($oldCart);
        foreach ($request->all() as $product) {
            $productId = $product["id"];
            $quantity = $product["quantity"];

            $theProduct = Product::find($productId);

            $cart->add($theProduct, $productId, $quantity);
        }
        DB::beginTransaction();

        try {
            $order = new Order();
            $order->user_id = $userId;
            $order->total_price = $cart->totalPrice;
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
                    return response(["message" => "Not enough quantity for product: " . $product->name], 404);
                }
                $product->update([
                    "count" => $product->count - $item["quantity"],
                    "sold_count" => $product->sold_count + $item["quantity"],
                ]);
            }
            Session::forget("cart" . (string)$userId);
            DB::commit();

            $userFcmToken = User::find($userId)->fcm_token;
            FulfillOrder::dispatch($order->id, $userFcmToken)->delay(now()->addMinutes(1));

            return response(["message" => "Order placed successfully"], 200);
        } catch (\Exception $err) {
            DB::rollBack();
            return response(["message" => "Something went wrong, plz try again!", "error" => $err->getMessage()], 500);
        }
    }


    public function getOrders()
    {
        $userId = Auth::user()->id;
        $user = User::find($userId);

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
        $products = [];
        $totalPrice = 0;
        if (!$request->all()) return response(["message" => "nothing to give you"], 404);

        foreach ($request['items'] as $item) {
            $product = Product::find($item["id"]);
            $info = ["name" => $product->name, "price" => $product->price * $item["quantity"], "quantity" => $item["quantity"]];
            array_push($products, $info);
            $totalPrice += $product->price * $item["quantity"];
        }

        array_push($products, ["totalPrice" => $totalPrice]);


        return response($products, 200);
    }
}
