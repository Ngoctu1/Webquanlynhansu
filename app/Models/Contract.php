<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = ['contract_code', 'employee_id', 'previous_contract_id', 'contract_type', 'signed_date', 'effective_from', 'effective_to', 'base_salary', 'salary_coefficient', 'allowance', 'status', 'created_by', 'submitted_by', 'submitted_at', 'approved_by', 'approved_at', 'rejection_reason'];

    public function scopeEffectiveOn($query, $date)
    {
        return $query->where('status', 'approved')->whereDate('effective_from', '<=', $date)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date));
    }

    protected function casts(): array
    {
        return ['signed_date' => 'date', 'effective_from' => 'date', 'effective_to' => 'date', 'base_salary' => 'decimal:2', 'salary_coefficient' => 'decimal:3', 'allowance' => 'decimal:2', 'submitted_at' => 'datetime', 'approved_at' => 'datetime'];
    }

    public function employee() { return $this->belongsTo(Employee::class); }
    public function previousContract() { return $this->belongsTo(Contract::class, 'previous_contract_id'); }
    public function amendments() { return $this->hasMany(Contract::class, 'previous_contract_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function submitter() { return $this->belongsTo(User::class, 'submitted_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
    public function payrollDetails() { return $this->hasMany(PayrollDetail::class); }
}
