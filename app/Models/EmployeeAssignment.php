<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'department_id',
        'position_id',
        'effective_from',
        'effective_to',
        'changed_by',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function changer()
    {
        return $this->belongsTo(
            User::class,
            'changed_by'
        );
    }
}
