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
        Schema::create('position_salaries', function (Blueprint $table) {
             $table->id();

            $table->foreignId('position_id')
                ->constrained('positions')
                ->cascadeOnDelete();

            $table->decimal('base_salary', 15, 2);
            $table->decimal('coefficient', 5, 2)->default(1);

            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('position_salaries');
    }
};
