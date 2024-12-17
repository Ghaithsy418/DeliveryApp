<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        "name",
        "ingredients",
        "type",
        "price",
        "count",
        "sold_count",
        "store_id",
        "image_source",
    ];

    public function store()
    {
        return $this->hasOne(Store::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, "users_products_pivot");
    }

    public function favorites(){
        return $this->hasMany(Favorite::class);
    }

}
