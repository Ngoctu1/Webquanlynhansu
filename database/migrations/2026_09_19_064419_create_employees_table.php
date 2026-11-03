<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code', 20)->unique();
            $table->string('full_name', 150);
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth');
            $table->string('citizen_id', 12)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('personal_email', 150)->nullable()->unique();
            $table->string('work_email', 150)->unique();
            $table->string('address', 255)->nullable();
            $table->string('avatar', 255)->nullable();

            $table->foreignId('department_id')->nullable()->index()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->index()->constrained('positions')->nullOnDelete();
            $table->date('hire_date');
            $table->enum('employment_status', ['pending', 'working', 'on_leave', 'resigned'])->default('pending')->index();
            $table->unsignedTinyInteger('annual_leave_days')->default(12);

            $table->date('resignation_date')->nullable();
            $table->text('resignation_reason')->nullable();

            $table->enum('approval_status', ['pending_approval', 'approved', 'rejected'])->default('pending_approval');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
