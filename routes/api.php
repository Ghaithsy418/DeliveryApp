<?php

use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\api\v1\ProductController;
use App\Http\Controllers\api\v1\StoreController;
use App\Http\Controllers\api\v1\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::group(["prefix" => "v1"], function () {
    Route::group(["middleware" => ["auth:sanctum"]], function () {

        /*
            ################### User Routes (Authentication required) ###################
        */
        Route::apiResource("users", UserController::class);
        Route::post("/logout", [UserController::class, "logout"]);
        Route::get("/search/{name}", [UserController::class, "search"]);
        Route::get("/purchases", [UserController::class, "purchasedProducts"]);
        Route::post("/user-update", [UserController::class, "update"]);
        Route::get("/show-curr-user",[UserController::class,"show"]);

        // ################################################################################

        /*
        ################### Product Routes (Authentication required) ###################
        */
        Route::apiResource("products", ProductController::class);
        Route::middleware(["startsession", "shareerrors"])->group(function () {
            Route::post("/add-to-cart/{id}", [ProductController::class, "AddToCart"]);
            Route::post("/add-all-to-cart", [ProductController::class, "addAllToCart"]);
            Route::get("get-cart", [ProductController::class, "GetCart"]);
            Route::delete("/delete-cart-product/{id}", [ProductController::class, "DeleteCartProduct"]);
            Route::post("/product-update/{id}", [ProductController::class, "update"]);
            Route::post("/purchase-products", [ProductController::class, "purchase"]);
            Route::get("/get-orders",[ProductController::class,"getOrders"]);
            Route::get("/categories/{type}", [ProductController::class, "categories"]);
            Route::post("/get-invoice",[ProductController::class,"getInvoice"]);
        });

        // ################################################################################


        /*
            ################### Store Routes (Authentication required) ###################
        */
        Route::apiResource("stores", StoreController::class);
        Route::delete("/delete-all/{id}", [StoreController::class, "destroyAll"]);
        Route::post("/store-update/{id}", [StoreController::class, "update"]);

        // ################################################################################

        /*
            ################## Favorite Routes (Authentication required) ##################
        */

        Route::post("/add-favorite/{id}", [FavoriteController::class, "addFavorite"]);
        Route::get("/get-favorite", [FavoriteController::class, "getFavorite"]);
    });

    // User Routes (NO Auth required)
    Route::middleware(["startsession", "shareerrors"])->group(function () {
        Route::post("/register", [UserController::class, "register"]);
        Route::post("/login", [UserController::class, "basicLogin"]);
        Route::post("/admin-login",[UserController::class,"adminLogin"]);
    });
});
