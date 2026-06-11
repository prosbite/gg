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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();

            // The "Overall" Status
            $table->enum('status', ['reserved', 'picked_up', 'returned', 'cancelled', 'completed'])
                ->default('reserved');

            // Dates apply to the whole group of items
            $table->date('pickup_date');
            $table->date('return_date'); // pickup + 2 days
            $table->timestamp('actual_returned_at')->nullable();

            $table->text('internal_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
