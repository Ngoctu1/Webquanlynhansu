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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            $table->string('name', 100)->unique();
            $table->text('description')->nullable();

            $table->enum('status', [
                'active',
                'inactive'
            ])->default('active');
            $table->timestamps();
            // $table->tinyInteger('status')->default(1); // Cũ 1: Hoạt động, 0: Khóa
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
