<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function assignments()
    {
        return $this->hasMany(EmployeeAssignment::class);
    }
}
