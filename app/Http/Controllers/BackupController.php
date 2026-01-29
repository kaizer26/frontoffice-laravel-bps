<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    public function download()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $dbPath = database_path('database.sqlite');
        
        if (!file_exists($dbPath)) {
            return redirect()->back()->with('error', 'Database file tidak ditemukan!');
        }

        $fileName = 'backup_fo_bps_' . date('Y-m-d_H-i-s') . '.sqlite';
        
        ActivityLogger::log('BACKUP_DATABASE', null, null, "Mengunduh backup database: $fileName");

        return Response::download($dbPath, $fileName);
    }
}
