<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionSalary extends Model
{
    use HasFactory;

    protected $fillable = [
        'position_id',
        'base_salary',
        'coefficient',
        'effective_from',
        'effective_to',
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

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}