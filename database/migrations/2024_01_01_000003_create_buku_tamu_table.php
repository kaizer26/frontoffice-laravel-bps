<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buku_tamu', function (Blueprint $table) {
            $table->id();
            $table->dateTime('waktu_kunjungan');
            $table->string('nama_konsumen');
            $table->string('instansi');
            $table->string('no_hp', 20);
            $table->string('email');
            $table->text('jenis_layanan');
            $table->text('keperluan');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->index('waktu_kunjungan');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku_tamu');
    }
};
