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
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();

            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');

            $table->decimal('standard_work_days', 5, 2);

            $table->enum('status', [
                'draft',
                'calculated',
                'approved',
                'paid'
            ])->default('draft');

            $table->foreignId('calculated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('calculated_at')->nullable();

            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->unique([
                'month',
                'year'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
