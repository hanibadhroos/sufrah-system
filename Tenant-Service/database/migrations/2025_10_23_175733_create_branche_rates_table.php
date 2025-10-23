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
        Schema::create('branche_rates', function (Blueprint $table) {
            $table->id();
            $table->uuid('brache_id')->foreignId()->nullable();
            $table->uuid('customer_id')->foreignId()->nullable();
            $table->string('rate');
            $table->text('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branche_rates');
    }
};
