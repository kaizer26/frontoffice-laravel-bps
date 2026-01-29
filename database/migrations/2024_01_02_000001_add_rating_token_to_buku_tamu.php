<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('buku_tamu', function (Blueprint $table) {
            $table->string('rating_token', 64)->nullable()->unique()->after('user_id');
            $table->boolean('rated')->default(false)->after('rating_token');
        });
    }

    public function down(): void
    {
        Schema::table('buku_tamu', function (Blueprint $table) {
            $table->dropColumn(['rating_token', 'rated']);
        });
    }
};
