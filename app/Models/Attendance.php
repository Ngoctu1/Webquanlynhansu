<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'work_date', 'check_in', 'check_out', 'late_minutes', 'work_units', 'overtime_hours', 'regular_minutes', 'overtime_minutes', 'overtime_type', 'overtime_status', 'overtime_approved_by', 'status', 'adjustment_reason', 'adjusted_by'];

    protected function casts(): array
    {
        return ['work_date' => 'date', 'check_in' => 'datetime', 'check_out' => 'datetime', 'late_minutes' => 'integer', 'regular_minutes' => 'integer', 'overtime_minutes' => 'integer', 'work_units' => 'decimal:4', 'overtime_hours' => 'decimal:2'];
    }

    public function employee() { return $this->belongsTo(Employee::class); }
    public function overtimeApprover() { return $this->belongsTo(User::class, 'overtime_approved_by'); }
    public function adjuster() { return $this->belongsTo(User::class, 'adjusted_by'); }
}
