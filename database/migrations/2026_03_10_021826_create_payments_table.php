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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->onDelete('cascade');

            // 1. The Amount
            $table->decimal('amount', 10, 2);

            // 2. The "What" (Type)
            // downpayment: To lock the reservation
            // balance: Paid upon pickup
            // security_deposit: The refundable portion
            // penalty: Late fees or damage fees
            $table->enum('type', ['downpayment', 'balance', 'security_deposit', 'penalty']);

            // 3. The "How" (Method)
            $table->enum('method', ['cash', 'gcash', 'bank_transfer'])->default('cash');

            // 4. The "Paper Trail"
            $table->string('reference_number')->nullable(); // For GCash/Bank trans
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
