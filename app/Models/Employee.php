<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code',
        'full_name',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'address',
        'identity_number',
        'hire_date',
        'department_id',
        'position_id',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function salaries()
    {
        return $this->hasMany(EmployeeSalary::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrollDetails()
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function assignments()
    {
        return $this->hasMany(EmployeeAssignment::class);
    }
}
