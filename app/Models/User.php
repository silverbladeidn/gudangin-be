<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_blocked',
        'role_id', // legacy 1:1
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_blocked' => 'boolean',
    ];

    /**
     * Relasi ke Role tradisional (1 user = 1 role)
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Override hasRole() Spatie supaya tetap mendukung role_id tradisional
     */
    public function hasRole($roles): bool
    {
        if (is_string($roles)) {
            return $this->role && $this->role->name === $roles;
        }

        if (is_array($roles)) {
            return $this->role && in_array($this->role->name, $roles);
        }

        return false;
    }


    /**
     * Cek apakah user aktif (tidak diblok)
     */
    public function isActive(): bool
    {
        return !$this->is_blocked;
    }

    /**
     * Scope untuk filter user berdasarkan role (legacy)
     */
    public function scopeWithRole($query, string $roleName)
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Scope untuk user yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_blocked', false);
    }
}
