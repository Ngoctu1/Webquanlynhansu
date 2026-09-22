<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('employee_id')
                ->nullable()
                ->unique()
                ->constrained('employees')
                ->nullOnDelete();

            $table->foreignId('role_id')
                ->constrained('roles')
                ->restrictOnDelete();

            $table->string('username', 50)->unique();

            $table->string('password')->nullable();

            $table->enum('status', [
                'pending',
                'active',
                'locked',
                'disabled'
            ])->default('pending');

            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();

            $table->rememberToken();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
