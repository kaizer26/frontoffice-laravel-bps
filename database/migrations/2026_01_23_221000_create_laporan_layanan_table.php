<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_layanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buku_tamu_id')->unique()->constrained('buku_tamu')->onDelete('cascade');
            
            // Common fields for all service types
            $table->string('topik')->nullable();
            $table->text('ringkasan')->nullable();
            $table->text('foto_bukti')->nullable(); // JSON array of file paths
            $table->string('feedback_final')->nullable(); // Puas, Perlu penjelasan lanjut, dll
            
            // Specific to Permintaan Data
            $table->string('surat_balasan')->nullable(); // Path to reply letter PDF
            $table->string('arsip_layanan')->nullable(); // Path to archived request letter
            $table->text('tags')->nullable(); // JSON array of data tags
            
            $table->timestamps();
            
            $table->index('buku_tamu_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_layanan');
    }
};
