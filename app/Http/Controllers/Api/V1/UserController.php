<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\RegisterRequest;
use App\Http\Resources\V1\ProductCollection;
use Illuminate\Http\Request;
use App\Http\Resources\V1\UserResource;
use App\Http\Resources\V1\UserCollection;
use App\Http\Requests\V1\UpdateUserRequest;
use App\Models\Cart;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new UserCollection(User::all());
    }


    public function Login(Request $request)
    {
        $data = $request->validate([
            "phone" => "required",
            "password" => "required",
        ]);

        $user = User::where("phone", $data["phone"])->first();

        if (!$user || !Hash::check($data["password"], $user->password)) {
            return response([
                "message" => "Your phone or password isn't correct plz try again",
            ], 401);
        }

        $token = $user->createToken("myToken")->plainTextToken;

        $user["token"] = $token;
        $user->save();

        $user_datas = new UserResource(User::find($user->id));

        return response([$user_datas], 200);
    }


    /**
     * Register a new User
     */
    public function Register(RegisterRequest $request)
    {
        $user = new UserResource(User::create([
            "first_name" => $request->firstName,
            "last_name" => $request->lastName,
            "phone" => $request->phone,
            "location" => $request->location,
            "password" => bcrypt($request->password),
        ]));

        $token = $user->createToken("myToken")->plainTextToken;

        $user["token"] = $token;
        $user->save();

        return response([$user], 201);
    }

    /*
    LogingOut from the app :(
    */
    public function Logout(Request $request)
    {
        // $request->user()->currentAccessToken()->delete();
        $accessToken = $request->bearerToken();
        $token = PersonalAccessToken::find($accessToken);
        $token->delete();

        return response([
            "message" => "You logged out Successfully"
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request)
    {
        $user_id = Auth::user()->id;
        $user = User::find($user_id);

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

        return response(["The Stores" => $stores, "The Products" => $products], 200);
    }
}
