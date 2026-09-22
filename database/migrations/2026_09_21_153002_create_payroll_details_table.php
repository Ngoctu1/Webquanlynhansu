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
        Schema::create('payroll_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payroll_period_id')
                ->constrained('payroll_periods')
                ->cascadeOnDelete();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->decimal('base_salary', 15, 2);
            $table->decimal('coefficient', 5, 2);

            $table->decimal('work_days', 5, 2);
            $table->decimal('standard_days', 5, 2);

            $table->decimal('allowance', 15, 2)->default(0);
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('deduction', 15, 2)->default(0);

            $table->decimal('gross_salary', 15, 2);
            $table->decimal('net_salary', 15, 2);

            $table->text('note')->nullable();

            $table->timestamps();

            $table->unique([
                'payroll_period_id',
                'employee_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};
