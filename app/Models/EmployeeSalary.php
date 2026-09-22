<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'base_salary',
        'coefficient',
        'effective_from',
        'effective_to',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'coefficient' => 'decimal:2',
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}
