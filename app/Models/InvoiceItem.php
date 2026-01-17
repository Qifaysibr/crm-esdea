<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'product_id',
        'line_order',
        'product_name',
        'description',
        'unit_price',
        'base_price',
        'quantity',
        'discount_amount',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'base_price' => 'decimal:2',
        'quantity' => 'integer',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    // Calculate refund (selisih harga jual - harga dasar)
    public function getRefundAttribute()
    {
        return $this->unit_price - $this->base_price;
    }
}
