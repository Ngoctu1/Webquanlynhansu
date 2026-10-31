<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE `leave_requests` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `leave_type` ENUM('annual','sick','personal','unpaid') NOT NULL,
    `reason` TEXT NOT NULL,
    `status` ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
    `reviewed_by` BIGINT UNSIGNED NULL,
    `reviewed_at` DATETIME NULL,
    `rejection_reason` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_leave_requests_employee_status_date` (`employee_id`, `status`, `start_date`, `end_date`),
    KEY `idx_leave_requests_status_date` (`status`, `start_date`, `end_date`),
    KEY `idx_leave_requests_reviewer` (`reviewed_by`),
    CONSTRAINT `fk_leave_requests_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_leave_requests_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `chk_leave_request_dates` CHECK (`end_date` >= `start_date`),
    CONSTRAINT `chk_leave_request_review` CHECK (
        (`status` IN ('pending','cancelled') AND `reviewed_by` IS NULL AND `reviewed_at` IS NULL)
        OR (`status` IN ('approved','rejected') AND `reviewed_by` IS NOT NULL AND `reviewed_at` IS NOT NULL)
    ),
    CONSTRAINT `chk_leave_request_rejection_reason` CHECK (`status` <> 'rejected' OR CHAR_LENGTH(TRIM(COALESCE(`rejection_reason`, ''))) > 0)
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
