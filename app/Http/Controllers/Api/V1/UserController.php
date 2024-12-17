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
                "message" => "bad Datas",
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

        return [
            "message" => "you logged out Successfully"
        ];
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
    public function update(UpdateUserRequest $request, string $id)
    {
        User::find($id)->update([$request->all(), "password" => bcrypt($request->password)]);
    }

    public function purchasedProducts()
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
