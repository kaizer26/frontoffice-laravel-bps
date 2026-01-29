<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nip_bps', 9)->unique();
            $table->string('nip_pns', 18)->unique();
            $table->string('nama');
            $table->string('jabatan')->nullable();
            $table->string('golongan', 50)->nullable();
            $table->string('email_bps')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
