<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\CommissionService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected $commissionService;

    public function __construct(CommissionService $commissionService)
    {
        $this->commissionService = $commissionService;
    }

    public function index(Request $request)
    {
        $query = Invoice::with(['lead', 'creator', 'quotation']);
        
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->where('created_by', $user->id);
        }
        
        $invoices = $query->latest()->paginate(20);
        
        return view('invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['items', 'lead', 'creator', 'quotation', 'commissions.user']);
        
        return view('invoices.show', compact('invoice'));
    }

    public function updatePayment(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:unpaid,partial,paid',
            'paid_amount' => 'required|numeric|min:0',
            'paid_date' => 'required_if:payment_status,paid,partial|nullable|date',
            'payment_notes' => 'nullable|string',
        ]);

        $oldStatus = $invoice->payment_status;

        $invoice->update($validated);

        // If status changed to paid, calculate commissions
        if ($oldStatus !== 'paid' && $validated['payment_status'] === 'paid') {
            try {
                $this->commissionService->calculateForInvoice($invoice);
            } catch (\Exception $e) {
                return back()->withErrors(['error' => 'Invoice updated but commission calculation failed: ' . $e->getMessage()]);
            }
        }

        return back()->with('success', 'Payment status updated successfully');
    }
}
