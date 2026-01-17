<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
        'product_id',
        'commission_type',
        'commission_value',
        'min_transaction',
        'max_transaction',
        'is_active',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'min_transaction' => 'decimal:2',
        'max_transaction' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
