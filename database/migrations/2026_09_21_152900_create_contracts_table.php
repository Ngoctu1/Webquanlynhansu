<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_code', 50)->unique();
            $table->foreignId('employee_id')->index()->constrained('employees')->cascadeOnDelete();
            $table->foreignId('previous_contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->enum('contract_type', ['probation', 'fixed_term', 'indefinite']);
            $table->date('signed_date')->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable()->index();
            $table->decimal('base_salary', 15, 2);
            $table->decimal('coefficient', 5, 2)->default(1.00);
            $table->decimal('allowance', 15, 2)->default(0);
            $table->text('allowance_note')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->enum('status', ['active', 'expired', 'terminated'])->default('active');
            $table->enum('approval_status', ['draft', 'pending_approval', 'approved', 'rejected'])->default('pending_approval');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
