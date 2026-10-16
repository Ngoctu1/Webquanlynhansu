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
CREATE TABLE `employees` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_code` VARCHAR(20) NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `gender` ENUM('male','female','other') NULL,
    `date_of_birth` DATE NULL,
    `phone` VARCHAR(20) NULL,
    `personal_email` VARCHAR(255) NOT NULL,
    `work_email` VARCHAR(255) NULL,
    `address` VARCHAR(255) NULL,
    `department_id` BIGINT UNSIGNED NOT NULL,
    `position_id` BIGINT UNSIGNED NOT NULL,
    `work_schedule_id` BIGINT UNSIGNED NOT NULL,
    `hire_date` DATE NULL,
    `employment_status` ENUM('pending','working','on_leave','resigned') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_employees_code` (`employee_code`),
    UNIQUE KEY `uq_employees_personal_email` (`personal_email`),
    UNIQUE KEY `uq_employees_work_email` (`work_email`),
    KEY `idx_employees_department` (`department_id`),
    KEY `idx_employees_schedule` (`work_schedule_id`),
    KEY `idx_employees_position` (`position_id`),
    CONSTRAINT `fk_employees_schedule` FOREIGN KEY (`work_schedule_id`) REFERENCES `work_schedules` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_employees_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_employees_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
