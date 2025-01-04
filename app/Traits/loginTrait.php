<?php
namespace App\Traits;

use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait LoginTrait {
    public function loginTrait($request,$status){
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

        if($status[1] === "create" && $user->role !== "admin") return response(["message" => "you are not supposed to be here"],403);
        if($status[1] === "none" && $user->role !== "user") {
            $status[0] = "adminToken";
            $status[1] = "create";
        }
        $token = $user->createToken("{$status[0]}",[$status[1]])->plainTextToken;

        $user["token"] = $token;
        $user->save();

        $user_datas = new UserResource(User::find($user->id));
        return response($user_datas, 200);
    }
}












?>
