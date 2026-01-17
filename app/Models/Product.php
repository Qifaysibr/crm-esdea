<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'code',
        'description',
        'base_price',
        'sell_price',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function commissionRules()
    {
        return $this->hasMany(CommissionRule::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
