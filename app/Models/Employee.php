<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = ['employee_code', 'full_name', 'gender', 'date_of_birth', 'phone', 'personal_email', 'work_email', 'address', 'department_id', 'position_id', 'work_schedule_id', 'hire_date', 'employment_status'];

    protected function casts(): array
    {
        return ['date_of_birth' => 'date', 'hire_date' => 'date'];
    }

    public function workSchedule() { return $this->belongsTo(WorkSchedule::class); }
    public function department() { return $this->belongsTo(Department::class); }
    public function position() { return $this->belongsTo(Position::class); }
    public function user() { return $this->hasOne(User::class); }
    public function contracts() { return $this->hasMany(Contract::class); }
    public function attendances() { return $this->hasMany(Attendance::class); }
    public function payrollDetails() { return $this->hasMany(PayrollDetail::class); }
}
