<?php

namespace App\Http\Controllers;

use App\Models\DataRegistry;
use Illuminate\Http\Request;
use App\Helpers\ActivityLogger;

class DataRegistryController extends Controller
{
    public function index()
    {
        $registries = DataRegistry::withCount('entries')->latest()->paginate(15);
        return view('admin.data-registry.index', compact('registries'));
    }

    public function create()
    {
        return view('admin.data-registry.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'satuan' => 'nullable|string|max:100',
            'periode_tipe' => 'required|in:tahunan,semesteran,triwulanan,bulanan',
            'layout_type' => 'required|in:vertical,horizontal',
            'numeric_format' => 'required|in:id,en',
            'decimal_places' => 'required|integer|min:0|max:4',
            'sumber_data' => 'nullable|string|max:255',
            'link_spreadsheet' => 'nullable|string|url|max:255',
            'template_json' => 'required|json',
        ]);

        $registry = DataRegistry::create($validated);

        ActivityLogger::log('CREATE_DATA_REGISTRY', 'DataRegistry', $registry->id, "Membuat tabel data baru: {$registry->judul}");

        return redirect()->route('admin.data-registry.index')
            ->with('success', 'Tabel data berhasil dibuat!');
    }

    public function show(DataRegistry $dataRegistry)
    {
        $dataRegistry->load('entries');
        return view('admin.data-registry.show', compact('dataRegistry'));
    }

    public function edit(DataRegistry $dataRegistry)
    {
        return view('admin.data-registry.edit', compact('dataRegistry'));
    }

    public function update(Request $request, DataRegistry $dataRegistry)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'satuan' => 'nullable|string|max:100',
            'periode_tipe' => 'required|in:tahunan,semesteran,triwulanan,bulanan',
            'layout_type' => 'required|in:vertical,horizontal',
            'numeric_format' => 'required|in:id,en',
            'decimal_places' => 'required|integer|min:0|max:4',
            'sumber_data' => 'nullable|string|max:255',
            'link_spreadsheet' => 'nullable|string|url|max:255',
            'template_json' => 'required|json',
        ]);

        $dataRegistry->update($validated);

        ActivityLogger::log('UPDATE_DATA_REGISTRY', 'DataRegistry', $dataRegistry->id, "Mengupdate template tabel: {$dataRegistry->judul}");

        return redirect()->route('admin.data-registry.index')
            ->with('success', 'Template tabel berhasil diupdate!');
    }

    public function destroy(DataRegistry $dataRegistry)
    {
        $judul = $dataRegistry->judul;
        $dataRegistry->delete();

        ActivityLogger::log('DELETE_DATA_REGISTRY', 'DataRegistry', null, "Menghapus tabel data: {$judul}");

        return redirect()->route('admin.data-registry.index')
            ->with('success', 'Tabel data berhasil dihapus!');
    }

    public function viewer(DataRegistry $registry)
    {
        // Get all entries with their data
        $entries = $registry->entries()->orderBy('periode')->get();
        
        return view('admin.data-registry.viewer', compact('registry', 'entries'));
    }
}
