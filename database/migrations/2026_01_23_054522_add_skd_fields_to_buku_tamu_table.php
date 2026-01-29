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
            $table->string('skd_token')->nullable()->unique()->after('rating_token');
            $table->boolean('skd_filled')->default(false)->after('skd_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buku_tamu', function (Blueprint $table) {
            $table->dropColumn(['skd_token', 'skd_filled']);
        });
    }
};
