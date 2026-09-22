<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'year',
        'standard_work_days',
        'status',
        'calculated_by',
        'calculated_at',
        'approved_by',
        'approved_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'standard_work_days' => 'decimal:2',
            'calculated_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function details()
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function calculator()
    {
        return $this->belongsTo(
            User::class,
            'calculated_by'
        );
    }

    public function approver()
    {
        return $this->belongsTo(
            User::class,
            'approved_by'
        );
    }
}
