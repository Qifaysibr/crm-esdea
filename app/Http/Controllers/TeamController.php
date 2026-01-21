<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Lead;
use App\Models\Invoice;
use App\Models\Commission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Only Manager and Admin can access
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            abort(403, 'Unauthorized access');
        }
        
        // Get team members
        $query = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['sales', 'leader']);
        });
        
        // Filter by store for non-admin
        if (!$user->hasRole('admin') && $user->store) {
            $query->where('store', $user->store);
        }
        
        $teamMembers = $query->get();
        
        // Get performance metrics for each member
        $performance = [];
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        foreach ($teamMembers as $member) {
            $performance[] = [
                'user' => $member,
                'metrics' => $this->getMemberMetrics($member, $currentYear, $currentMonth),
            ];
        }
        
        return view('team.index', compact('performance'));
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $query = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['sales', 'leader']);
        });
        
        if (!$user->hasRole('admin') && $user->store) {
            $query->where('store', $user->store);
        }
        
        $members = $query->get();
        $month = now()->month;
        $year = now()->year;

        $filename = "team_performance_" . now()->format('Y_m_d') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Name', 'Store', 'Total Leads', 'Conv. Rate (%)', 'Total Sales', 'Commission Earned'];

        $callback = function() use($members, $columns, $month, $year) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($members as $member) {
                $m = $this->getMemberMetrics($member, $year, $month);
                fputcsv($file, [
                    $member->name,
                    $member->store ?? 'Global',
                    $m['total_leads'],
                    $m['conversion_rate'],
                    $m['total_sales'],
                    $m['total_commission']
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show(User $user)
    {
        $authUser = auth()->user();
        
        // Check permissions
        if (!$authUser->hasRole('admin') && !$authUser->hasRole('manager')) {
            abort(403, 'Unauthorized access');
        }
        
        // Get detailed metrics
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $metrics = $this->getMemberMetrics($user, $currentYear, $currentMonth);
        
        // Get recent activities
        $recentLeads = Lead::where('assigned_to', $user->id)
                          ->latest('last_activity_at')
                          ->limit(10)
                          ->get();
        
        $recentInvoices = Invoice::where('created_by', $user->id)
                                ->latest()
                                ->limit(10)
                                ->get();
        
        return view('team.show', compact('user', 'metrics', 'recentLeads', 'recentInvoices'));
    }

    private function getMemberMetrics($user, $year, $month)
    {
        // Total leads
        $totalLeads = Lead::where('assigned_to', $user->id)
                         ->whereYear('created_at', $year)
                         ->whereMonth('created_at', $month)
                         ->count();
        
        // Leads by status
        $leadsByStatus = Lead::select('status_id', DB::raw('count(*) as count'))
                            ->where('assigned_to', $user->id)
                            ->whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->groupBy('status_id')
                            ->with('status')
                            ->get();
        
        // Total sales (paid invoices)
        $totalSales = Invoice::where('created_by', $user->id)
                            ->where('payment_status', 'paid')
                            ->whereYear('paid_date', $year)
                            ->whereMonth('paid_date', $month)
                            ->sum('total');
        
        // Total commission earned
        $totalCommission = Commission::where('user_id', $user->id)
                                    ->whereYear('calculated_at', $year)
                                    ->whereMonth('calculated_at', $month)
                                    ->sum('commission_amount');
        
        // Conversion rate
        $convertedLeads = Lead::where('assigned_to', $user->id)
                             ->whereHas('status', function($q) {
                                 $q->where('name', 'sales');
                             })
                             ->whereYear('created_at', $year)
                             ->whereMonth('created_at', $month)
                             ->count();
        
        $conversionRate = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;
        
        // Target achievement
        $target = $user->targets()
                      ->where('year', $year)
                      ->where('month', $month)
                      ->first();
        
        return [
            'total_leads' => $totalLeads,
            'leads_by_status' => $leadsByStatus,
            'total_sales' => $totalSales,
            'total_commission' => $totalCommission,
            'conversion_rate' => round($conversionRate, 2),
            'target' => $target,
        ];
    }
}
