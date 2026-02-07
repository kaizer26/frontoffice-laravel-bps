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
            $table->string('link_spreadsheet')->nullable()->after('sumber_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_registries', function (Blueprint $table) {
            $table->dropColumn('link_spreadsheet');
        });
    }
};
