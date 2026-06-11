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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Split names for better sorting and searching
            $table->string('first_name')->index();
            $table->string('last_name')->index();
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('address')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('social_media_link')->nullable();
            $table->string('identification')->nullable();
            $table->json('detail_info')->nullable();
            $table->boolean('is_blacklisted')->default(false);
            $table->text('admin_notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
