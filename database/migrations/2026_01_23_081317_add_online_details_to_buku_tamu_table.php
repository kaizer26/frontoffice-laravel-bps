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
        Schema::table('buku_tamu', function (Blueprint $table) {
            $table->string('online_channel')->nullable()->after('sarana_kunjungan'); // 'Pegawai' atau 'Kontak Admin'
            $table->foreignId('petugas_online_id')->nullable()->after('online_channel')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buku_tamu', function (Blueprint $table) {
            $table->dropConstrainedForeignId('petugas_online_id');
            $table->dropColumn('online_channel');
        });
    }
};
