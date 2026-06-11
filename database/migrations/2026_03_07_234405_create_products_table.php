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
            $table->string('item_code')->unique()->index();
            $table->string('name')->unique();

            $table->foreignId('category_id')->constrained()->nullable();

            // Override prices (Null = use Category price)
            $table->decimal('custom_rental_fee', 10, 2)->nullable();
            $table->decimal('custom_security_deposit', 10, 2)->nullable();

            // Current physical state of the item
            $table->enum('status', ['available', 'rented', 'maintenance', 'retired'])
                ->default('available')
                ->index();

            $table->text('description')->nullable();
            $table->json('specifics')->nullable(); // Size, Color, etc.
            $table->boolean('is_active')->default(true);
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
