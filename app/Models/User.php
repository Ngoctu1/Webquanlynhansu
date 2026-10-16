<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = ['employee_id', 'role_id', 'email', 'password', 'status', 'activation_token_hash', 'activation_expires_at', 'activated_at', 'last_login_at'];

    protected $hidden = ['password', 'remember_token', 'activation_token_hash'];

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role?->name, $roles, true);
    }

    public function homePath(): string
    {
        return $this->canManageHrm() ? '/admin' : '/user';
    }

    public function canManageHrm(): bool
    {
        return $this->hasRole(Role::HR, Role::DIRECTOR, Role::ADMIN);
    }

    public function canApprove(): bool
    {
        return $this->hasRole(Role::DIRECTOR, Role::ADMIN);
    }

    protected function casts(): array
    {
        return ['password' => 'hashed', 'activation_expires_at' => 'datetime', 'activated_at' => 'datetime', 'last_login_at' => 'datetime'];
    }

    public function employee() { return $this->belongsTo(Employee::class); }
    public function role() { return $this->belongsTo(Role::class); }
    public function createdContracts() { return $this->hasMany(Contract::class, 'created_by'); }
    public function submittedContracts() { return $this->hasMany(Contract::class, 'submitted_by'); }
    public function approvedContracts() { return $this->hasMany(Contract::class, 'approved_by'); }
    public function auditLogs() { return $this->hasMany(AuditLog::class, 'user_id'); }
}
