<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Exact MySQL/MariaDB DDL from the approved ten-table schema.
        DB::unprepared(<<<'SQL'
CREATE TABLE `attendances` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `work_date` DATE NOT NULL,
    `check_in` DATETIME NULL,
    `check_out` DATETIME NULL,
    `late_minutes` INT UNSIGNED NOT NULL DEFAULT 0,
    `work_units` DECIMAL(6,4) NOT NULL DEFAULT 0.00,
    `overtime_hours` DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    `regular_minutes` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `overtime_minutes` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `overtime_type` ENUM('weekday','rest_day','holiday') NULL,
    `overtime_status` ENUM('none','pending','approved','rejected') NOT NULL DEFAULT 'none',
    `overtime_approved_by` BIGINT UNSIGNED NULL,
    `status` ENUM('checked_in','completed','adjusted') NOT NULL DEFAULT 'checked_in',
    `adjustment_reason` TEXT NULL,
    `adjusted_by` BIGINT UNSIGNED NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_attendances_employee_date` (`employee_id`, `work_date`),
    KEY `idx_attendances_overtime_approver` (`overtime_approved_by`),
    KEY `idx_attendances_adjusted_by` (`adjusted_by`),
    CONSTRAINT `fk_attendances_overtime_approver` FOREIGN KEY (`overtime_approved_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_attendances_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_attendances_adjusted_by` FOREIGN KEY (`adjusted_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `chk_attendance_times` CHECK (`check_out` IS NULL OR (`check_in` IS NOT NULL AND `check_out` >= `check_in`)),
    CONSTRAINT `chk_attendance_units` CHECK (`work_units` >= 0 AND `work_units` <= 1 AND `overtime_hours` >= 0)
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
