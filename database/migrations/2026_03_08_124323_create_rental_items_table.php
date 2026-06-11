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
        Schema::create('rental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained(); // GW-01, etc.

            // We snapshot the price per item here
            $table->decimal('rental_fee', 10, 2);
            $table->decimal('security_deposit', 10, 2);

            // Item-specific status (In case they return the Gown but forget the Veil)
            $table->enum('status', ['reserved', 'picked_up', 'returned'])->default('reserved');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_items');
    }
};
