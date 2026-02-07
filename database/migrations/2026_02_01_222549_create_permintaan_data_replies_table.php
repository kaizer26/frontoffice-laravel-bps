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
        Schema::create('permintaan_data_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permintaan_data_id')->constrained('permintaan_data')->onDelete('cascade');
            $table->string('nomor_surat')->unique();
            $table->integer('nomor_urut');
            $table->string('tujuan');
            $table->string('perihal')->nullable();
            $table->date('tanggal_surat');
            $table->string('kode_surat'); // record the code used at the time
            $table->text('catatan')->nullable();
            $table->string('file_surat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintaan_data_replies');
    }
};
