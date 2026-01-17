<?php

namespace App\Http\Controllers;

use App\Models\MarketingAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MarketingAssetController extends Controller
{
    public function index(Request $request)
    {
        $query = MarketingAsset::with('uploader');
        
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $assets = $query->latest()->paginate(12);
        
        $categories = MarketingAsset::select('category')
                                   ->distinct()
                                   ->whereNotNull('category')
                                   ->pluck('category');
        
        return view('marketing-assets.index', compact('assets', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'file' => 'required|file|max:51200', // Max 50MB
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('marketing-assets', $filename, 'public');

        MarketingAsset::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'file_name' => $filename,
            'file_path' => $path,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Asset berhasil diupload');
    }

    public function download(MarketingAsset $asset)
    {
        $asset->incrementDownload();
        
        return Storage::disk('public')->download($asset->file_path, $asset->file_name);
    }

    public function destroy(MarketingAsset $asset)
    {
        // Only admin or uploader can delete
        if (!auth()->user()->hasRole('admin') && $asset->uploaded_by !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        Storage::disk('public')->delete($asset->file_path);
        $asset->delete();

        return back()->with('success', 'Asset berhasil dihapus');
    }
}
