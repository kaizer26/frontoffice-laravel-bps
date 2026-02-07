<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku_tamu_handlers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buku_tamu_id')->constrained('buku_tamu')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('Membantu'); // e.g., 'Pengumpul Data', 'Konsultan', etc.
            $table->timestamps();

            $table->unique(['buku_tamu_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_tamu_handlers');
    }
};
