<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_period_id',
        'employee_id',
        'base_salary',
        'coefficient',
        'work_days',
        'standard_days',
        'allowance',
        'overtime_pay',
        'deduction',
        'gross_salary',
        'net_salary',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'coefficient' => 'decimal:2',
            'work_days' => 'decimal:2',
            'standard_days' => 'decimal:2',
            'allowance' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'deduction' => 'decimal:2',
            'gross_salary' => 'decimal:2',
            'net_salary' => 'decimal:2',
        ];
    }

    public function period()
    {
        return $this->belongsTo(
            PayrollPeriod::class,
            'payroll_period_id'
        );
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
