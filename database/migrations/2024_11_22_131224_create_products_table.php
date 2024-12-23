<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string("name",25);
            $table->text("description");
            $table->string("category",25); // Food , Electronics , Gifts ......
            $table->integer("price");
            $table->integer("count");
            $table->integer("sold_count")->default(0);
            $table->integer("store_id");
            $table->string("image_source")->default("");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
