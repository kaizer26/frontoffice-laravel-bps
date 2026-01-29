<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PetugasController extends Controller
{
    public function dashboard()
    {
        // Get all active officers for selection dropdowns
        $petugas = \App\Models\User::where('role', 'petugas')
            ->where('status', 'aktif')
            ->orderBy('name')
            ->get();
            
        return view('petugas.dashboard', compact('petugas'));
    }
}
