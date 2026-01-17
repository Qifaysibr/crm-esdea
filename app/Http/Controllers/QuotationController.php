<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Lead;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\QuotationPDFService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    protected $pdfService;

    public function __construct(QuotationPDFService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    public function index(Request $request)
    {
        $query = Quotation::with(['lead', 'creator']);
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $user = auth()->user();
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->where('created_by', $user->id);
        }
        
        $quotations = $query->latest()->paginate(20);
        
        return view('quotations.index', compact('quotations'));
    }

    public function create(Request $request)
    {
        $leads = Lead::where('assigned_to', auth()->id())->get();
        $products = Product::active()->get();
        
        $lead = null;
        if ($request->filled('lead_id')) {
            $lead = Lead::find($request->lead_id);
        }
        
        return view('quotations.create', compact('leads', 'products', 'lead'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'nullable|exists:leads,id',
            'customer_name' => 'required|string',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string',
            'customer_company' => 'nullable|string',
            'quotation_date' => 'required|date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        
        try {
            // Create quotation
            $quotation = Quotation::create([
                'quotation_number' => Quotation::generateNumber(),
                'lead_id' => $validated['lead_id'],
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'customer_company' => $validated['customer_company'],
                'created_by' => auth()->id(),
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => date('Y-m-d', strtotime($validated['quotation_date'] . ' +14 days')),
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'status' => 'draft',
                'notes' => $validated['notes'],
                'terms' => $validated['terms'] ?? $this->getDefaultTerms(),
            ]);
            
            // Create quotation items
            foreach ($validated['items'] as $index => $item) {
                $product = Product::find($item['product_id']);
                
                $quotationItem = new QuotationItem([
                    'product_id' => $item['product_id'],
                    'line_order' => $index + 1,
                    'product_name' => $product->name,
                    'description' => $product->description,
                    'notes' => $item['notes'] ?? '',
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'discount_percentage' => $item['discount_percentage'] ?? 0,
                ]);
                
                $quotationItem->calculateSubtotal();
                $quotation->items()->save($quotationItem);
            }
            
            // Calculate totals
            $quotation->calculateTotals();
            
            DB::commit();
            
            return redirect()->route('quotations.show', $quotation)
                           ->with('success', 'Quotation berhasil dibuat');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['items', 'lead', 'creator']);
        
        return view('quotations.show', compact('quotation'));
    }

    public function generatePDF(Quotation $quotation)
    {
        return $this->pdfService->stream($quotation);
    }

    public function downloadPDF(Quotation $quotation)
    {
        return $this->pdfService->download($quotation);
    }

    public function convertToInvoice(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return back()->withErrors(['error' => 'Quotation sudah dikonversi ke invoice']);
        }

        DB::beginTransaction();
        
        try {
            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateNumber(),
                'quotation_id' => $quotation->id,
                'lead_id' => $quotation->lead_id,
                'created_by' => $quotation->created_by,
                'customer_name' => $quotation->customer_name,
                'customer_email' => $quotation->customer_email,
                'customer_phone' => $quotation->customer_phone,
                'customer_company' => $quotation->customer_company,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'subtotal' => $quotation->subtotal,
                'discount_amount' => $quotation->discount_amount,
                'total' => $quotation->total,
                'payment_status' => 'unpaid',
            ]);
            
            // Copy items
            foreach ($quotation->items as $index => $item) {
                $product = $item->product;
                
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item->product_id,
                    'line_order' => $index + 1,
                    'product_name' => $item->product_name,
                    'description' => $item->description,
                    'unit_price' => $item->unit_price,
                    'base_price' => $product ? $product->base_price : 0,
                    'quantity' => $item->quantity,
                    'discount_amount' => $item->discount_amount,
                    'subtotal' => $item->subtotal,
                ]);
            }
            
            // Update quotation status
            $quotation->update(['status' => 'converted']);
            
            // Update lead status to 'sales' if exists
            if ($quotation->lead) {
                $salesStatus = \App\Models\LeadStatus::where('name', 'sales')->first();
                if ($salesStatus) {
                    $quotation->lead->update([
                        'status_id' => $salesStatus->id,
                        'last_activity_at' => now(),
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('invoices.show', $invoice)
                           ->with('success', 'Quotation berhasil dikonversi ke Invoice');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error: ' . $e->getMessage()]);
        }
    }

    private function getDefaultTerms()
    {
        return "1. Pembayaran DP 60% setelah penandatanganan kontrak\n" .
               "2. Pelunasan H+1 setelah pekerjaan selesai\n" .
               "3. Pembayaran via transfer ke Bank Mandiri a/n PT Esdea Assistance Management\n" .
               "4. Quotation ini berlaku selama 14 hari sejak tanggal terbit";
    }
}
