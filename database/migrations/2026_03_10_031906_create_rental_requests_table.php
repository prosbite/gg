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
        Schema::create('rental_requests', function (Blueprint $table) {
            $table->id();

            // 1. Customer Input (Raw)
            $table->string('first_name');
            $table->string('last_name');
            $table->string('contact_number')->nullable();
            $table->string('fb_profile_link')->nullable();

            // 2. The Gown they are holding (They can type the "GW-01" from the tag)
            $table->string('item_code_requested')->nullable();

            // 3. The Date they need it for
            $table->date('requested_pickup_date')->nullable();
            $table->json('images')->nullable();
            $table->text('notes')->nullable();

            // 4. Processing Status
            // pending, approved (moved to draft/rental), rejected
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_requests');
    }
};
