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
        Schema::create('attendances', function (Blueprint $table) {
           $table->id();

            $table->foreignId('employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->date('work_date');

            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();

            $table->decimal('work_hours', 5, 2)->nullable();

            $table->enum('status', [
                'present',
                'late',
                'leave',
                'absent',
                'holiday'
            ])->default('present');

            $table->string('note')->nullable();

            $table->timestamps();

            $table->unique([
                'employee_id',
                'work_date'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
