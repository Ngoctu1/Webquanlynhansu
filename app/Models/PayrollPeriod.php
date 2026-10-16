<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $fillable = ['month', 'year', 'standard_work_days', 'status', 'created_by', 'submitted_by', 'submitted_at', 'approved_by', 'approved_at', 'rejection_reason', 'published_at'];

    protected function casts(): array
    {
        return ['month' => 'integer', 'year' => 'integer', 'standard_work_days' => 'decimal:2', 'submitted_at' => 'datetime', 'approved_at' => 'datetime', 'published_at' => 'datetime'];
    }

    public function details() { return $this->hasMany(PayrollDetail::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function submitter() { return $this->belongsTo(User::class, 'submitted_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }
}
