<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaian_petugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buku_tamu_id')->constrained('buku_tamu')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->tinyInteger('rating_keramahan');
            $table->tinyInteger('rating_kecepatan');
            $table->tinyInteger('rating_pengetahuan');
            $table->tinyInteger('rating_keseluruhan');
            $table->text('komentar')->nullable();
            $table->timestamps();
            
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaian_petugas');
    }
};
