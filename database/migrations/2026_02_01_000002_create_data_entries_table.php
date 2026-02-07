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
        Schema::create('data_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('data_registry_id')->constrained()->onDelete('cascade');
            $table->string('periode'); // '2024', '2024-S1', '2024-Q1', etc.
            $table->text('data_json'); // Actual data in Handsontable format
            $table->timestamps();
            
            $table->unique(['data_registry_id', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_entries');
    }
};
