<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\RegisterRequest;
use App\Http\Resources\V1\ProductCollection;
use App\Http\Resources\V1\StoreCollection;
use App\Services\FCMService;
use App\Traits\LoginTrait;
use Illuminate\Http\Request;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\UserCollection;
use App\Http\Requests\V1\UpdateUserRequest;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class UserController extends Controller
{
    use LoginTrait;
    public function index()
    {
        return new UserCollection(User::all());
    }

    public function basicLogin(Request $request)
    {
        return $this->loginTrait($request, ["basicToken", "none"]);
    }

    public function adminLogin(Request $request)
    {
        return $this->loginTrait($request, ["adminToken", "create"]);
    }


    /**
     * Register a new User
     */
    public function register(RegisterRequest $request)
    {
        $user = new UserResource(User::create([
            "first_name" => $request->firstName,
            "last_name" => $request->lastName,
            "phone" => $request->phone,
            "location" => $request->location,
            "password" => bcrypt($request->password),
        ]));

        $token = $user->createToken("basicToken", ["none"])->plainTextToken;

        if ($request->fcmToken) {
            $user["fcm_token"] = $request->fcmToken;
            $fcmService = new FCMService();
            $fcmService->singleNotification($request->fcmToken, "Welcome", "Welcome to our Great Shamify Application💚");
        }

        $user->save();

        return response([["id" => $user->id, "firstName" => $user->first_name, "lastName" => $user->last_name, "phone" => $user->phone, "location" => $user->location, "token" => $token]], 201);
    }

    /*
    LogingOut from the app :(
    */
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->fcm_token = null;
        $request->user()->currentAccessToken()->delete();
        $user->save();

        return response([
            "message" => "You logged out Successfully"
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $userId = Auth::user()->id;
        $user = User::find($userId);
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request)
    {
        $userId = Auth::user()->id;
        $user = User::find($userId);

        if (!$user) {
            return response([
                "message" => "didn't find the user",
            ], 404);
        }

        $user->update($request->all());
        $user->save();

        return new UserResource($user);
    }

    public function purchasedProducts(): mixed
    {
        $user = Auth::user();
        return $user->products;
    }

    public function search($name)
    {
        $stores = Store::where("name", "like", "%" . $name . "%")->get();
        $products = Product::where("name", "like", "%" . $name . "%")->get();

        $storesCollection = new StoreCollection($stores);
        $productsCollection = new ProductCollection($products);

        return response(["The Stores" => $storesCollection, "The Products" => $productsCollection], 200);
    }
}
