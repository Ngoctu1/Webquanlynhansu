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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('employee_code', 20)->unique();
            $table->string('full_name', 150);

            $table->date('date_of_birth')->nullable();

            $table->enum('gender', [
                'male',
                'female',
                'other'
            ])->nullable();

            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->unique();
            $table->string('address')->nullable();
            $table->string('identity_number', 20)->nullable()->unique();

            $table->date('hire_date')->nullable();

            $table->foreignId('department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->foreignId('position_id')
                ->nullable()
                ->constrained('positions')
                ->nullOnDelete();

            $table->enum('status', [
                'working',
                'resigned',
                'inactive'
            ])->default('working');

            $table->timestamps();
            $table->softDeletes();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
