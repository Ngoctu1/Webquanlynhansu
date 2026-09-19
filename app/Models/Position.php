<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];
    //         $table->id();
    //         $table->string('name', 100); // Tên chức vụ (VD: Trưởng phòng, Nhân viên)
    //         $table->text('description')->nullable(); // Mô tả công việc
    //         $table->timestamps();
}
