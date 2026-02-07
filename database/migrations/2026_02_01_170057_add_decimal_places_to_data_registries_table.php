<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('data_registries', function (Blueprint $table) {
            $table->tinyInteger('decimal_places')->default(2)->after('numeric_format');
            // Number of decimal places to display (0-4)
        });
    }

    public function down(): void
    {
        Schema::table('data_registries', function (Blueprint $table) {
            $table->dropColumn('decimal_places');
        });
    }
};
