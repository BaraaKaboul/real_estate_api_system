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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->decimal('price',14,3);
            $table->smallInteger('area');
            $table->enum('type',['house','commercial']);
            $table->enum('purpose',['sale','rent']);
            $table->enum('status',['accept','denied','pending'])->default('pending');
            $table->string('phone');
            $table->string('address');
            $table->tinyInteger('balconies');
            $table->tinyInteger('bedrooms');
            $table->tinyInteger('bathrooms');
            $table->tinyInteger('livingRooms');
            $table->decimal('location_lat');
            $table->decimal('location_lon');
            $table->boolean('is_featured')->default(false);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
