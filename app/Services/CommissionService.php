<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    /**
     * Calculate and save commissions for an invoice
     */
    public function calculateForInvoice(Invoice $invoice)
    {
        if ($invoice->payment_status !== 'paid') {
            return false;
        }

        DB::beginTransaction();
        
        try {
            // Delete existing commissions for this invoice
            Commission::where('invoice_id', $invoice->id)->delete();
            
            $salesUser = $invoice->creator;
            
            foreach ($invoice->items as $item) {
                $refund = $item->unit_price - $item->base_price;
                
                // Calculate commission for each role the user has
                foreach ($salesUser->roles as $role) {
                    $this->calculateRoleCommission(
                        $invoice,
                        $item,
                        $salesUser,
                        $role,
                        $refund
                    );
                }
                
                // Double commission for Leader/Manager who also do direct sales
                if ($salesUser->hasAnyRole(['leader', 'manager'])) {
                    $this->calculateDoubleCommission($invoice, $item, $salesUser, $refund);
                }
            }
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate commission based on role rules
     */
    private function calculateRoleCommission($invoice, $item, $user, $role, $refund)
    {
        // Find commission rule for this role and product
        $rule = CommissionRule::where('role_id', $role->id)
                             ->where(function($q) use ($item) {
                                 $q->where('product_id', $item->product_id)
                                   ->orWhereNull('product_id');
                             })
                             ->where('is_active', true)
                             ->where('min_transaction', '<=', $item->subtotal)
                             ->where(function($q) use ($item) {
                                 $q->whereNull('max_transaction')
                                   ->orWhere('max_transaction', '>=', $item->subtotal);
                             })
                             ->first();

        if (!$rule) {
            // Default commission if no rule found
            $commissionAmount = $this->getDefaultCommission($role->name, $refund);
            $commissionRate = null;
        } else {
            if ($rule->commission_type === 'percentage') {
                $commissionAmount = ($item->subtotal * $rule->commission_value) / 100;
                $commissionRate = $rule->commission_value;
            } else {
                $commissionAmount = $rule->commission_value;
                $commissionRate = null;
            }
        }

        Commission::create([
            'invoice_id' => $invoice->id,
            'invoice_item_id' => $item->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'commission_type' => $role->name . '_commission',
            'transaction_amount' => $item->subtotal,
            'base_price' => $item->base_price,
            'refund_amount' => $refund,
            'commission_rate' => $commissionRate,
            'commission_amount' => $commissionAmount,
            'calculated_at' => now(),
        ]);
    }

    /**
     * Calculate double commission for Leader/Manager direct sales
     */
    private function calculateDoubleCommission($invoice, $item, $user, $refund)
    {
        // Leader/Manager get fixed commission per product PLUS sales commission
        $fixedCommission = $this->getFixedCommissionForRole($user, $item);
        
        if ($fixedCommission > 0) {
            $role = $user->roles()->whereIn('name', ['leader', 'manager'])->first();
            
            Commission::create([
                'invoice_id' => $invoice->id,
                'invoice_item_id' => $item->id,
                'user_id' => $user->id,
                'role_id' => $role->id,
                'commission_type' => $role->name . '_bonus',
                'transaction_amount' => $item->subtotal,
                'base_price' => $item->base_price,
                'refund_amount' => $refund,
                'commission_rate' => null,
                'commission_amount' => $fixedCommission,
                'notes' => 'Bonus komisi untuk ' . $role->display_name . ' yang melakukan direct sales',
                'calculated_at' => now(),
            ]);
        }
    }

    /**
     * Get default commission if no rule exists
     */
    private function getDefaultCommission($roleName, $refund)
    {
        // Simple default: 10% of refund for sales
        if ($roleName === 'sales') {
            return max(0, $refund * 0.10);
        }
        return 0;
    }

    /**
     * Get fixed commission for Leader/Manager
     */
    private function getFixedCommissionForRole($user, $item)
    {
        // Example: Rp 50,000 per product for Leader, Rp 100,000 for Manager
        if ($user->hasRole('manager')) {
            return 100000;
        } elseif ($user->hasRole('leader')) {
            return 50000;
        }
        return 0;
    }

    /**
     * Get total commission for a user in a period
     */
    public function getUserCommissionTotal($userId, $startDate = null, $endDate = null)
    {
        $query = Commission::where('user_id', $userId);
        
        if ($startDate) {
            $query->where('calculated_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('calculated_at', '<=', $endDate);
        }
        
        return $query->sum('commission_amount');
    }

    /**
     * Get potential commission from unpaid invoices
     */
    public function getPotentialCommission($userId)
    {
        $unpaidInvoices = Invoice::where('created_by', $userId)
                                ->whereIn('payment_status', ['unpaid', 'partial'])
                                ->get();
        
        $potential = 0;
        foreach ($unpaidInvoices as $invoice) {
            // Estimate commission based on current rules
            foreach ($invoice->items as $item) {
                $refund = $item->unit_price - $item->base_price;
                $potential += $this->getDefaultCommission('sales', $refund);
            }
        }
        
        return $potential;
    }
}
