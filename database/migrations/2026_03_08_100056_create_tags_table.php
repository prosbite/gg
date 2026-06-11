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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // 'Royal Blue', 'XL', 'Satin', 'Modern Filipiniana'
            $table->string('slug'); // 'royal-blue', 'xl', 'satin'
            $table->foreignId('type')->constrained('tag_types')->cascadeOnDelete();
            // Ensure we don't have two "Red" tags for the "color" type
            $table->unique(['name']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
