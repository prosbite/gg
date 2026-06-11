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
        Schema::create('draft_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draft_rental_id')->constrained()->onDelete('cascade');

            // Path to the file in storage/app/public/tmp/
            $table->string('file_path');

            // Help her identify which is which before finalizing
            $table->string('label')->nullable(); // e.g., "Main", "Back", "Stain-check"

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_images');
    }
};
