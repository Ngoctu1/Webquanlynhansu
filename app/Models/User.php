<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'employee_id',
        'role_id',
        'email',
        'password',
        'status',
        'activation_token',
        'token_expires_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'activation_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'token_expires_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === Role::ADMIN;
    }

    public function isDirector(): bool
    {
        return $this->role?->name === Role::DIRECTOR;
    }

    public function isHR(): bool
    {
        return $this->role?->name === Role::HR;
    }

    public function isEmployee(): bool
    {
        return $this->role?->name === Role::EMPLOYEE;
    }

    public function hasManagementAccess(): bool
    {
        return $this->isAdmin() || $this->isDirector() || $this->isHR();
    }

    public function needsApproval(): bool
    {
        if ($this->isHR()) {
            return true;
        }

        if ($this->isDirector() || $this->isAdmin()) {
            return false;
        }

        return true;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role?->name, $roles, true);
    }

    public function homePath(): string
    {
        return $this->hasManagementAccess() ? '/admin' : '/user';
    }

    public function canManageHrm(): bool
    {
        return $this->hasManagementAccess();
    }

    public function canApprove(): bool
    {
        return $this->hasRole(Role::DIRECTOR, Role::ADMIN);
    }

    public function approvedContracts()
    {
        return $this->hasMany(Contract::class, 'approved_by');
    }

    public function reviewedLeaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'reviewed_by');
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }
}
