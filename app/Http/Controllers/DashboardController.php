<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\Invoice;
use App\Models\Commission;
use App\Models\Target;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get date range (default: current month)
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // 1. Funnel Statistics
        $funnelStats = $this->getFunnelStatistics($user, $startDate, $endDate);
        
        // 2. Financial Analytics
        $financialStats = $this->getFinancialStatistics($user, $startDate, $endDate);
        
        // 3. Target Progress
        $targetProgress = $this->getTargetProgress($user);
        
        // 4. Sales Chart Data (daily trend)
        $salesChartData = $this->getSalesChartData($user, $startDate, $endDate);
        
        // 5. Leaderboard
        $leaderboard = $this->getLeaderboard($user);
        
        // 6. Stagnant Leads Alert
        $stagnantLeads = $this->getStagnantLeads($user);
        
        return view('dashboard', compact(
            'funnelStats',
            'financialStats',
            'targetProgress',
            'salesChartData',
            'leaderboard',
            'stagnantLeads'
        ));
    }

    private function getFunnelStatistics($user, $startDate, $endDate)
    {
        $query = Lead::whereBetween('created_at', [$startDate, $endDate]);
        
        // Filter by user role
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->where('assigned_to', $user->id);
        }
        
        $statuses = LeadStatus::orderBy('order')->get();
        $stats = [];
        
        foreach ($statuses as $status) {
            $count = (clone $query)->where('status_id', $status->id)->count();
            $stats[] = [
                'name' => $status->display_name,
                'count' => $count,
                'color' => $status->color,
            ];
        }
        
        return $stats;
    }

    private function getFinancialStatistics($user, $startDate, $endDate)
    {
        // Potensial Komisi (from unpaid/proforma invoices)
        $potentialQuery = Invoice::whereIn('payment_status', ['unpaid', 'partial'])
                                ->whereBetween('invoice_date', [$startDate, $endDate]);
        
        // Komisi Diraih (from paid invoices)
        $earnedQuery = Commission::whereBetween('calculated_at', [$startDate, $endDate]);
        
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $potentialQuery->where('created_by', $user->id);
            $earnedQuery->where('user_id', $user->id);
        }
        
        $potentialCommission = $potentialQuery->sum('total') * 0.10; // Estimate 10%
        $earnedCommission = $earnedQuery->sum('commission_amount');
        
        return [
            'potential' => $potentialCommission,
            'earned' => $earnedCommission,
        ];
    }

    private function getTargetProgress($user)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $target = Target::where('user_id', $user->id)
                       ->where('year', $currentYear)
                       ->where('month', $currentMonth)
                       ->first();
        
        if (!$target) {
            return [
                'target' => 0,
                'achieved' => 0,
                'percentage' => 0,
            ];
        }
        
        // Update achieved amount from paid invoices
        $achieved = Invoice::where('created_by', $user->id)
                          ->where('payment_status', 'paid')
                          ->whereYear('paid_date', $currentYear)
                          ->whereMonth('paid_date', $currentMonth)
                          ->sum('total');
        
        $target->achieved_amount = $achieved;
        $target->updateProgress();
        
        return [
            'target' => $target->target_amount,
            'achieved' => $target->achieved_amount,
            'percentage' => $target->achievement_percentage,
        ];
    }

    private function getSalesChartData($user, $startDate, $endDate)
    {
        $query = Invoice::where('payment_status', 'paid')
                       ->whereBetween('paid_date', [$startDate, $endDate]);
        
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->where('created_by', $user->id);
        }
        
        $dailySales = $query->select(
                            DB::raw('DATE(paid_date) as date'),
                            DB::raw('SUM(total) as total')
                        )
                        ->groupBy('date')
                        ->orderBy('date')
                        ->get();
        
        return [
            'labels' => $dailySales->pluck('date')->map(function($date) {
                return date('d M', strtotime($date));
            }),
            'data' => $dailySales->pluck('total'),
        ];
    }

    private function getLeaderboard($user)
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Get all sales users
        $query = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['sales', 'leader', 'manager']);
        });
        
        // Filter by store if user is not admin
        if (!$user->hasRole('admin') && $user->store) {
            $query->where('store', $user->store);
        }
        
        $users = $query->get();
        
        $leaderboard = [];
        foreach ($users as $u) {
            $totalSales = Invoice::where('created_by', $u->id)
                                ->where('payment_status', 'paid')
                                ->whereYear('paid_date', $currentYear)
                                ->whereMonth('paid_date', $currentMonth)
                                ->sum('total');
            
            $leaderboard[] = [
                'name' => $u->name,
                'store' => $u->store,
                'total_sales' => $totalSales,
            ];
        }
        
        // Sort by total sales descending
        usort($leaderboard, function($a, $b) {
            return $b['total_sales'] <=> $a['total_sales'];
        });
        
        return array_slice($leaderboard, 0, 10); // Top 10
    }

    private function getStagnantLeads($user)
    {
        $query = Lead::stagnant(3);
        
        if (!$user->hasRole('admin') && !$user->hasRole('manager')) {
            $query->where('assigned_to', $user->id);
        }
        
        return $query->with('status')
                    ->limit(5)
                    ->get();
    }
}
