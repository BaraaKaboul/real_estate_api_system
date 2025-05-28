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
        Schema::create('premia', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('office_name');
            $table->string('office_location');
            $table->string('phone');
            $table->string('about')->nullable();
            $table->enum('plan',['standard','pro','golden']);
            $table->enum('duration',['month','three month','year']);
            $table->enum('status',['accepted','denied','pending'])->default('pending');
            $table->unsignedInteger('max_featured')->default(0)->nullable();
            $table->unsignedInteger('used_featured')->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('premia');
    }
};
