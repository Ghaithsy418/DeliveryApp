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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name',25);
            $table->string("last_name",25);
            $table->string('phone');
            $table->string("location",50);
            $table->string('password');
            $table->string("token")->default("");
            $table->string("role")->default("user");
            $table->string("fcm_token")->nullable();
            $table->rememberToken();
            $table->timestamps();

            // indexes for improving the performance :)
                $table->index("phone");
                $table->index("token");
                $table->index("role");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
