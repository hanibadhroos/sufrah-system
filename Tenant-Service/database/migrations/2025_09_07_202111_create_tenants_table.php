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
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            // $table->string('username')->unique();
            $table->string('password');
            $table->string('logo');
            $table->enum('payment_method', ['cash', 'apple pay', 'cash + apple pay']);
            $table->string('location');
            $table->boolean('status')->default(true);
            $table->string('cancel_cutoff_minutes')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
