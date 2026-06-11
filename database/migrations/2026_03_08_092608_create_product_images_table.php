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
    Schema::create('product_images', function (Blueprint $table) {
        $table->id();

        // Link to the Gown (GW-01)
        $table->foreignId('product_id')->constrained()->onDelete('cascade');

        // Storage path (e.g., "products/GW-01-main.jpg")
        $table->string('file_path');
        $table->string('thumbnail_path')->nullable(); // Optional thumbnail version

        // Metadata
        $table->boolean('is_primary')->default(false); // The "Thumbnail" image
        $table->integer('sort_order')->default(0); // For arranging the gallery
        $table->string('label')->nullable(); // "Front View", "Close-up of Lace"

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
