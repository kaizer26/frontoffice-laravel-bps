<?php

namespace App\Http\Controllers;

use App\Models\JadwalPetugas;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PublicController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        $jadwal = JadwalPetugas::with('user')
            ->where('tanggal', $today)
            ->where('status', 'aktif')
            ->orderBy('shift')
            ->get()
            ->groupBy('shift');

        return view('public.index', compact('jadwal', 'today'));
    }
}
