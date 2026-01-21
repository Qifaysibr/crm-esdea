<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $criticalFields = ['status_id', 'assigned_to'];

    protected $fillable = [
        'name',
        'company',
        'email',
        'phone',
        'address',
        'notes',
        'status_id',
        'assigned_to',
        'last_activity_at',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
    ];

    // Relationships
    public function status()
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function activities()
    {
        return $this->hasMany(LeadActivity::class)->latest();
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    // Scopes
    public function scopeStagnant($query, $days = 3)
    {
        return $query->where('last_activity_at', '<', now()->subDays($days))
                     ->whereNotIn('status_id', function($q) {
                         $q->select('id')->from('lead_statuses')
                           ->whereIn('name', ['sales', 'lost']);
                     });
    }

    public function scopeByStatus($query, $statusName)
    {
        return $query->whereHas('status', function($q) use ($statusName) {
            $q->where('name', $statusName);
        });
    }

    // Helper methods
    public function getWhatsAppLinkAttribute()
    {
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '62' . substr($phone, 1);
        }
        $message = urlencode("Halo {$this->name}, saya dari PT Esdea Assistance Management.");
        return "https://wa.me/{$phone}?text={$message}";
    }
}
