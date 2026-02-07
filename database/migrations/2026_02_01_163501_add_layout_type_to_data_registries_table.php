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
        Schema::table('data_registries', function (Blueprint $table) {
            $table->string('layout_type', 20)->default('vertical')->after('periode_tipe');
            // vertical: tahun ke bawah (rows), horizontal: tahun ke kanan (columns)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_registries', function (Blueprint $table) {
            $table->dropColumn('layout_type');
        });
    }
};
