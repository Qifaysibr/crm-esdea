<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $criticalFields = ['total', 'status', 'discount_percentage'];

    protected $fillable = [
        'quotation_number',
        'lead_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_company',
        'created_by',
        'quotation_date',
        'valid_until',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'total',
        'status',
        'notes',
        'terms',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relationships
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
        return $this->hasMany(QuotationItem::class)->orderBy('line_order');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    // Auto-generate quotation number
    public static function generateNumber()
    {
        $year = date('Y');
        $month = strtoupper(date('M'));
        $romanMonth = self::getRomanMonth(date('n'));
        
        $lastQuotation = self::whereYear('quotation_date', $year)
                            ->whereMonth('quotation_date', date('m'))
                            ->latest('id')
                            ->first();
        
        $nextNumber = $lastQuotation ? (intval(substr($lastQuotation->quotation_number, 3, 3)) + 1) : 1;
        
        return sprintf('QT-%03d/Esdea/%s/%s', $nextNumber, $romanMonth, $year);
    }

    private static function getRomanMonth($month)
    {
        $romans = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        return $romans[$month - 1];
    }

    // Calculate totals
    public function calculateTotals()
    {
        $this->subtotal = $this->items->sum('subtotal');
        
        if ($this->discount_percentage > 0) {
            $this->discount_amount = ($this->subtotal * $this->discount_percentage) / 100;
        }
        
        $this->total = $this->subtotal - $this->discount_amount;
        $this->save();
    }
}
