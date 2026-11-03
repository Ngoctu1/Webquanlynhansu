<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;

    protected $fillable = [
        'contract_code',
        'employee_id',
        'previous_contract_id',
        'contract_type',
        'signed_date',
        'effective_from',
        'effective_to',
        'base_salary',
        'coefficient',
        'allowance',
        'allowance_note',
        'file_path',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'signed_date' => 'date',
            'effective_from' => 'date',
            'effective_to' => 'date',
            'base_salary' => 'decimal:2',
            'coefficient' => 'decimal:2',
            'allowance' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function scopeEffectiveOn($query, $date)
    {
        return $query->where('status', 'active')
            ->where('approval_status', 'approved')
            ->whereDate('effective_from', '<=', $date)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhereDate('effective_to', '>=', $date));
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function previousContract()
    {
        return $this->belongsTo(Contract::class, 'previous_contract_id');
    }

    public function amendments()
    {
        return $this->hasMany(Contract::class, 'previous_contract_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
