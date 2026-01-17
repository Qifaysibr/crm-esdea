<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'target_type',
        'period_type',
        'year',
        'month',
        'week',
        'target_amount',
        'achieved_amount',
        'achievement_percentage',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'achieved_amount' => 'decimal:2',
        'achievement_percentage' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function updateProgress()
    {
        if ($this->target_amount > 0) {
            $this->achievement_percentage = ($this->achieved_amount / $this->target_amount) * 100;
            $this->save();
        }
    }
}
