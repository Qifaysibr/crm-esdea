<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EarningsController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Date filter
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // Get commissions
        $query = Commission::with(['invoice', 'invoiceItem', 'role'])
                          ->whereBetween('calculated_at', [$startDate, $endDate]);
        
        // Filter by user if not admin/manager
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        $commissions = $query->latest('calculated_at')->get();
        
        // Group by invoice for transparency
        $earningsData = [];
        foreach ($commissions as $commission) {
            $invoiceId = $commission->invoice_id;
            
            if (!isset($earningsData[$invoiceId])) {
                $invoice = $commission->invoice;
                $earningsData[$invoiceId] = [
                    'invoice' => $invoice,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_name' => $invoice->customer_name,
                    'invoice_date' => $invoice->invoice_date,
                    'total_amount' => $invoice->total,
                    'items' => [],
                    'total_commission' => 0,
                ];
            }
            
            // Add item details
            if ($commission->invoiceItem) {
                $item = $commission->invoiceItem;
                $itemKey = $item->id;
                
                if (!isset($earningsData[$invoiceId]['items'][$itemKey])) {
                    $earningsData[$invoiceId]['items'][$itemKey] = [
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'base_price' => $item->base_price,
                        'refund' => $item->refund,
                        'subtotal' => $item->subtotal,
                        'commissions' => [],
                    ];
                }
                
                $earningsData[$invoiceId]['items'][$itemKey]['commissions'][] = [
                    'type' => $commission->commission_type,
                    'amount' => $commission->commission_amount,
                ];
            }
            
            $earningsData[$invoiceId]['total_commission'] += $commission->commission_amount;
        }
        
        // Summary statistics
        $totalEarned = collect($earningsData)->sum('total_commission');
        $totalTransactions = count($earningsData);
        
        return view('earnings.index', compact(
            'earningsData',
            'totalEarned',
            'totalTransactions',
            'startDate',
            'endDate'
        ));
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        $commissions = Commission::with(['invoice', 'invoiceItem', 'role'])
                                ->whereBetween('calculated_at', [$startDate, $endDate])
                                ->latest('calculated_at')
                                ->get();

        $filename = "earnings_report_{$startDate}_to_{$endDate}.csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Date', 'Invoice #', 'Customer', 'Product', 'Type', 'Amount', 'Refund Basis'];

        $callback = function() use($commissions, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($commissions as $comm) {
                fputcsv($file, [
                    $comm->calculated_at->format('Y-m-d'),
                    $comm->invoice->invoice_number,
                    $comm->invoice->customer_name,
                    $comm->invoiceItem->product_name ?? '-',
                    ucfirst($comm->commission_type),
                    $comm->commission_amount,
                    $comm->refund_amount
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
