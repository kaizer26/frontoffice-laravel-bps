<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buku_tamu_id')->constrained('buku_tamu')->onDelete('cascade');
            $table->string('nomor_surat', 100)->unique();
            $table->date('tanggal_surat');
            $table->string('file_surat')->nullable();
            $table->enum('status_layanan', [
                'Diterima', 
                'Diproses', 
                'Menunggu Persetujuan', 
                'Siap Diambil', 
                'Selesai'
            ])->default('Diterima');
            $table->dateTime('tanggal_update');
            $table->text('catatan')->nullable();
            $table->string('link_skd', 500)->nullable();
            $table->boolean('skd_terisi')->default(false);
            $table->timestamps();
            
            $table->index('status_layanan');
            $table->index('buku_tamu_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_data');
    }
};
