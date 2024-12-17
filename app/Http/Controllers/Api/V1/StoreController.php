<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Http\Requests\V1\StoreStoreRequest;
use App\Http\Requests\V1\UpdateStoreRequest;
use App\Http\Resources\V1\StoreResource;
use App\Http\Resources\V1\StoreCollection;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $storesQuery = Store::query();

        if ($request->query("withProducts")) {
            $storesQuery->with("products");
        }

        $stores = $storesQuery->paginate()->appends($request->query());

        return new StoreCollection($stores);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreStoreRequest $request)
    {
        return new StoreResource(Store::create($request->all()));
    }

    /**
     * Display the specified resource.
     */
    public function show(Store $store)
    {
        $includeProducts = Request()->query("withProducts");

        if ($includeProducts)
            return new StoreResource($store->loadMissing("products"));

        return new StoreResource($store);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStoreRequest $request, string $id)
    {
        $store = Store::find($id);
        $store->update($request->all());
        return new StoreResource($store);
    }

    public function destroy(string $id)
    {
        $store = Store::find($id);
        $store->delete();

        return response([
            "message" => "the store $store->name has been deleted successfully",
        ], 200);
    }

    public function destroyAll(string $id)
    {
        $store = Store::find($id);
        $products = $store->products;
        foreach ($products as $product) {
            $product->delete();
        }
        $store->delete();

        return response([
            "message" => "the store $store->name and it's products have been deleted successfully",
        ], 200);
    }
}
