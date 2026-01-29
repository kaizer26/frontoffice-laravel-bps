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
            $table->string('sarana_kunjungan')->default('Langsung')->after('keperluan')->comment('Langsung/Online');
        });
    }

    public function down(): void
    {
        Schema::table('buku_tamu', function (Blueprint $table) {
            $table->dropColumn('sarana_kunjungan');
        });
    }
};
