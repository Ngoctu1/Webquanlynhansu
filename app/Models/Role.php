<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public const HR = 'HR';
    public const DIRECTOR = 'Director';
    public const EMPLOYEE = 'Employee';
    public const ADMIN = 'Admin';


    public function users() { return $this->hasMany(User::class); }
}
