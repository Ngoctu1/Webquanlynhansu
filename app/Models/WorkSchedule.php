<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = ['name', 'start_time', 'end_time', 'break_start', 'break_end', 'standard_minutes', 'late_grace_minutes', 'work_days', 'status'];

    protected function casts(): array
    {
        return ['work_days' => 'array', 'standard_minutes' => 'integer', 'late_grace_minutes' => 'integer'];
    }

    public function employees() { return $this->hasMany(Employee::class); }
}
