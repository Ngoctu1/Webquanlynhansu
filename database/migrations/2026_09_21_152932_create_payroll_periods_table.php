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
CREATE TABLE `payroll_periods` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `month` TINYINT UNSIGNED NOT NULL,
    `year` SMALLINT UNSIGNED NOT NULL,
    `standard_work_days` DECIMAL(5,2) NOT NULL,
    `status` ENUM('draft','pending_approval','rejected','approved','published') NOT NULL DEFAULT 'draft',
    `created_by` BIGINT UNSIGNED NOT NULL,
    `submitted_by` BIGINT UNSIGNED NULL,
    `submitted_at` DATETIME NULL,
    `approved_by` BIGINT UNSIGNED NULL,
    `approved_at` DATETIME NULL,
    `rejection_reason` TEXT NULL,
    `published_at` DATETIME NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_payroll_period_year_month` (`year`, `month`),
    KEY `idx_payroll_created_by` (`created_by`),
    KEY `idx_payroll_submitted_by` (`submitted_by`),
    KEY `idx_payroll_approved_by` (`approved_by`),
    CONSTRAINT `fk_payroll_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_payroll_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_payroll_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `chk_payroll_period_range` CHECK (`month` BETWEEN 1 AND 12 AND `year` BETWEEN 2000 AND 2100 AND `standard_work_days` > 0 AND `standard_work_days` <= 31)
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
