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
            $table->string('numeric_format', 10)->default('id')->after('layout_type');
            // id: 1.234,56 (Indonesia), en: 1,234.56 (English/International)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_registries', function (Blueprint $table) {
            $table->dropColumn('numeric_format');
        });
    }
};
