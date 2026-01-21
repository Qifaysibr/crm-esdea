<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\LeadActivity;
use App\Services\LeadImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    protected $importService;

    public function __construct(LeadImportService $importService)
    {
        $this->importService = $importService;
    }

    public function index(Request $request)
    {
        $query = Lead::with(['status', 'assignedUser']);
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status_id', $request->status);
        }
        
        // Filter by assigned user (for non-admin)
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->where('assigned_to', $user->id);
        }
        
        $leads = $query->latest('last_activity_at')->paginate(20);
        $statuses = LeadStatus::orderBy('order')->get();
        
        return view('leads.index', compact('leads', 'statuses'));
    }

    public function create()
    {
        $statuses = LeadStatus::orderBy('order')->get();
        return view('leads.create', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status_id' => 'required|exists:lead_statuses,id',
        ]);

        $validated['assigned_to'] = auth()->id();
        $validated['last_activity_at'] = now();

        $lead = Lead::create($validated);
        
        // Log activity
        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'activity_type' => 'created',
            'description' => 'Lead dibuat',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead berhasil ditambahkan',
                'lead' => $lead,
            ]);
        }

        return redirect()->route('leads.index')
                        ->with('success', 'Lead berhasil ditambahkan');
    }

    public function edit(Lead $lead)
    {
        $statuses = LeadStatus::orderBy('order')->get();
        $activities = $lead->activities()->with('user')->latest()->get();
        
        if (request()->expectsJson()) {
            return response()->json([
                'lead' => $lead,
                'statuses' => $statuses,
                'activities' => $activities,
            ]);
        }
        
        return view('leads.edit', compact('lead', 'statuses', 'activities'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'status_id' => 'required|exists:lead_statuses,id',
            'activity_note' => 'nullable|string',
        ]);

        $oldStatus = $lead->status_id;
        
        $lead->update([
            'name' => $validated['name'],
            'company' => $validated['company'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'notes' => $validated['notes'],
            'status_id' => $validated['status_id'],
            'last_activity_at' => now(),
        ]);
        
        // Log activity
        if ($oldStatus != $validated['status_id']) {
            $oldStatusName = LeadStatus::find($oldStatus)->display_name;
            $newStatusName = LeadStatus::find($validated['status_id'])->display_name;
            
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'activity_type' => 'status_changed',
                'description' => "Status diubah dari {$oldStatusName} ke {$newStatusName}",
            ]);
        }
        
        if ($request->filled('activity_note')) {
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'activity_type' => 'note_added',
                'description' => $validated['activity_note'],
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Lead berhasil diupdate',
                'lead' => $lead->fresh(),
            ]);
        }

        return redirect()->route('leads.index')
                        ->with('success', 'Lead berhasil diupdate');
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Lead berhasil dihapus',
        ]);
    }

    public function downloadTemplate()
    {
        $filePath = $this->importService->generateTemplate();
        
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
        ]);

        $file = $request->file('file');
        $filePath = $file->getRealPath();
        
        try {
            $result = $this->importService->import($filePath, auth()->id());
            
            $message = $result['failed'] > 0 
                ? "{$result['imported']} lead berhasil diimport, {$result['failed']} gagal."
                : "{$result['imported']} lead berhasil diimport.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'errors' => $result['errors'] ?? [],
                    'imported' => $result['imported'],
                    'failed' => $result['failed'],
                ]);
            }

            return back()->with('success', $message);
            
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage(),
                ], 500);
            }
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
