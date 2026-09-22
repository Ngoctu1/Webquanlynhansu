<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'work_date',
        'check_in',
        'check_out',
        'work_hours',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'work_hours' => 'decimal:2',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}