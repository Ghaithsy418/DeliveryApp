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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string("name",25);
            $table->string("type",25); //Supermarket , Restaurant , GiftShop ......
            $table->text("description");
            $table->string("location",50);
            $table->string("image_source")->default("");
            $table->timestamps();

            //indexes for improving the performance
            $table->index("name");
            $table->index("type");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
