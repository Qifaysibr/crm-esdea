<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'invoice_item_id',
        'user_id',
        'role_id',
        'commission_type',
        'transaction_amount',
        'base_price',
        'refund_amount',
        'commission_rate',
        'commission_amount',
        'notes',
        'calculated_at',
    ];

    protected $casts = [
        'transaction_amount' => 'decimal:2',
        'base_price' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'calculated_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
