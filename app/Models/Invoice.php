<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $criticalFields = ['total', 'payment_status', 'paid_amount'];

    protected $fillable = [
        'invoice_number',
        'quotation_id',
        'lead_id',
        'created_by',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'invoice_date',
        'due_date',
        'subtotal',
        'discount_amount',
        'total',
        'payment_status',
        'paid_amount',
        'paid_date',
        'notes',
        'payment_notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    // Relationships
    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('line_order');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    // Auto-generate invoice number from quotation
    public static function generateNumber()
    {
        $year = date('Y');
        $month = strtoupper(date('M'));
        $romanMonth = self::getRomanMonth(date('n'));
        
        $lastInvoice = self::whereYear('invoice_date', $year)
                          ->whereMonth('invoice_date', date('m'))
                          ->latest('id')
                          ->first();
        
        $nextNumber = $lastInvoice ? (intval(substr($lastInvoice->invoice_number, 4, 3)) + 1) : 1;
        
        return sprintf('INV-%03d/Esdea/%s/%s', $nextNumber, $romanMonth, $year);
    }

    private static function getRomanMonth($month)
    {
        $romans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $romans[$month - 1];
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }
}
