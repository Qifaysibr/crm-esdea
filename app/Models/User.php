<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, Auditable;

    protected $criticalFields = ['email', 'is_active', 'store'];

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'store',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'created_by');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'created_by');
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function targets()
    {
        return $this->hasMany(Target::class);
    }

    // Helper methods
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }
        return $this->roles->contains($role);
    }

    public function hasAnyRole($roles)
    {
        return $this->roles->whereIn('name', $roles)->isNotEmpty();
    }

    public function hasPermission($permission)
    {
        foreach ($this->roles as $role) {
            $permissions = json_decode($role->permissions, true) ?? [];
            if (in_array($permission, $permissions)) {
                return true;
            }
        }
        return false;
    }
}
