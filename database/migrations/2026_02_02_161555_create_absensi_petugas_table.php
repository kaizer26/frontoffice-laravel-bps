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
        Schema::create('absensi_petugas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('jadwal_id')->nullable()->constrained('jadwal_petugas')->onDelete('set null');
            $table->date('tanggal');
            $table->dateTime('jam_masuk');
            $table->dateTime('jam_pulang')->nullable();
            $table->string('status_masuk')->default('Tepat Waktu'); // Tepat Waktu, Terlambat
            $table->string('ip_address')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_petugas');
    }
};
