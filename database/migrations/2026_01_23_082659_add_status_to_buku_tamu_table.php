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
            $table->string('status_layanan')->default('Diterima')->after('petugas_online_id');
            $table->dateTime('tanggal_update')->nullable()->after('status_layanan');
            $table->text('catatan')->nullable()->after('tanggal_update');
        });

        // Sync existing statuses from permintaan_data if any
        $dataRows = DB::table('permintaan_data')->get();
        foreach ($dataRows as $row) {
            DB::table('buku_tamu')
                ->where('id', $row->buku_tamu_id)
                ->update([
                    'status_layanan' => $row->status_layanan,
                    'tanggal_update' => $row->tanggal_update,
                    'catatan' => $row->catatan
                ]);
        }

        // For rows without permintaan_data, set initial update time
        DB::table('buku_tamu')
            ->whereNull('tanggal_update')
            ->update(['tanggal_update' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('buku_tamu', function (Blueprint $table) {
            $table->dropColumn(['status_layanan', 'tanggal_update', 'catatan']);
        });
    }
};
