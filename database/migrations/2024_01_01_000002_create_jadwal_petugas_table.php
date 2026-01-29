<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_petugas', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('shift', 50);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['aktif', 'libur', 'cuti'])->default('aktif');
            $table->timestamps();
            
            $table->index(['tanggal', 'shift']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_petugas');
    }
};
