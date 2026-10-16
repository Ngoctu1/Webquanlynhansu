<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration
{
    public function up(): void
    {
        // Authorized extension to the original ten-table SQL: one fixed schedule per employee.
        DB::unprepared(<<<'SQL'
CREATE TABLE work_schedules (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_start TIME NOT NULL,
    break_end TIME NOT NULL,
    standard_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 480,
    late_grace_minutes SMALLINT UNSIGNED NOT NULL DEFAULT 5,
    work_days JSON NOT NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_schedule_times CHECK (start_time < break_start AND break_start < break_end AND break_end < end_time),
    CONSTRAINT chk_schedule_minutes CHECK (standard_minutes > 0 AND late_grace_minutes < standard_minutes)
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
