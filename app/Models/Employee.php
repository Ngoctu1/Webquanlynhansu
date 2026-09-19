<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'employee_code',
        'full_name',
        'date_of_birth',
        'gender',
        'phone',
        'email',
        'address',
        'cccd',
        'hire_date',
        'department_id',
        'position_id',
        'status',
        //     $table->string('employee_code', 20)->unique(); // <<UK>>
        //     $table->string('full_name', 150);
        //     $table->date('date_of_birth')->nullable();
        //     $table->string('gender', 10)->nullable();
        //     $table->string('phone', 20)->nullable();
        //     $table->string('email', 150)->nullable();
        //     $table->string('address', 255)->nullable();
        //     $table->string('cccd', 20)->unique(); // <<UK>> (CCCD/CMND)
        //     $table->date('hire_date')->nullable();
            
        //     // Khóa ngoại (FK)
        //     $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
        //     $table->foreignId('position_id')->nullable()->constrained('positions')->onDelete('set null');
            
        //     $table->enum('status', ['Đang làm việc', 'Nghỉ phép', 'Đã nghỉ việc', 'Thử việc']) ->default('Đang làm việc');
        //     $table->timestamps();
    ];
}

