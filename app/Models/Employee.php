<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code',
        'full_name',
        'gender',
        'date_of_birth',
        'citizen_id',
        'phone',
        'personal_email',
        'work_email',
        'address',
        'avatar',
        'department_id',
        'position_id',
        'hire_date',
        'employment_status',
        'annual_leave_days',
        'resignation_date',
        'resignation_reason',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'resignation_date' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function activeContract()
    {
        return $this->hasOne(Contract::class)->where('status', 'active');
    }

    public function employeeDocuments()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isAdminOrDirector(): bool
    {
        $roleName = $this->user?->role?->name;

        return in_array($roleName, [Role::ADMIN, Role::DIRECTOR], true);
    }

    public function scopeVisibleTo(Builder $query, User $viewer): Builder
    {
        if ($viewer->isAdmin() || $viewer->isDirector()) {
            return $query;
        }

        if ($viewer->isHR()) {
            return $query->whereDoesntHave('user.role', function (Builder $q) {
                $q->whereIn('name', [Role::ADMIN, Role::DIRECTOR]);
            });
        }

        if ($viewer->isEmployee()) {
            return $query->where('employees.id', $viewer->employee_id);
        }

        return $query->whereRaw('1 = 0');
    }
}
