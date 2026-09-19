<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    protected $fillable = [
        'employee_id',
        'role_id',
        'username',
        'password',
        'status',
        'last_login_at',
        //     $table->id();
        //     // Khóa ngoại (FK)
        //     // employee_id có thể null vì tài khoản Super Admin đôi khi không nằm trong danh sách nhân viên
        //     $table->foreignId('employee_id')->nullable()->constrained('employees')->onDelete('cascade');
        //     $table->foreignId('role_id')->constrained('roles')->onDelete('restrict');
        //     $table->string('username', 50)->unique(); // <<UK>>
        //     $table->string('password', 255);
        //     $table->tinyInteger('status')->default(1); // 1: Active, 0: Bị khóa
        //     $table->timestamp('last_login_at')->nullable();
        //     $table->timestamps();
    ];

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the role associated with the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
