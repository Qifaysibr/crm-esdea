<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'product_id',
        'line_order',
        'product_name',
        'description',
        'notes',
        'unit_price',
        'quantity',
        'discount_percentage',
        'discount_amount',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Calculate subtotal
    public function calculateSubtotal()
    {
        $lineTotal = $this->unit_price * $this->quantity;
        
        if ($this->discount_percentage > 0) {
            $this->discount_amount = ($lineTotal * $this->discount_percentage) / 100;
        }
        
        $this->subtotal = $lineTotal - $this->discount_amount;
    }
}
