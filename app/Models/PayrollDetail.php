<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollDetail extends Model
{
    use HasFactory;

    protected $fillable = ['payroll_period_id', 'employee_id', 'contract_id', 'base_salary', 'salary_coefficient', 'allowance', 'standard_days', 'actual_days', 'overtime_pay', 'deduction', 'gross_salary', 'net_salary', 'note', 'calculation_snapshot'];

    protected function casts(): array
    {
        return ['calculation_snapshot' => 'array', 'base_salary' => 'decimal:2', 'allowance' => 'decimal:2', 'standard_days' => 'decimal:2', 'actual_days' => 'decimal:4', 'overtime_pay' => 'decimal:2', 'deduction' => 'decimal:2', 'gross_salary' => 'decimal:2', 'net_salary' => 'decimal:2', 'salary_coefficient' => 'decimal:3'];
    }

    public function period() { return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id'); }
    public function employee() { return $this->belongsTo(Employee::class); }
    public function contract() { return $this->belongsTo(Contract::class); }
}
