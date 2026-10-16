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
CREATE TABLE `payroll_details` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payroll_period_id` BIGINT UNSIGNED NOT NULL,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `contract_id` BIGINT UNSIGNED NOT NULL,
    `base_salary` DECIMAL(15,2) NOT NULL,
    `salary_coefficient` DECIMAL(6,3) NOT NULL DEFAULT 1.000,
    `allowance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `standard_days` DECIMAL(5,2) NOT NULL,
    `actual_days` DECIMAL(7,4) NOT NULL DEFAULT 0.00,
    `overtime_pay` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `deduction` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `gross_salary` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `net_salary` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `note` VARCHAR(255) NULL,
    `calculation_snapshot` JSON NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_payroll_detail_employee_period` (`payroll_period_id`, `employee_id`),
    KEY `idx_payroll_details_employee` (`employee_id`),
    KEY `idx_payroll_details_contract` (`contract_id`),
    CONSTRAINT `fk_payroll_detail_period` FOREIGN KEY (`payroll_period_id`) REFERENCES `payroll_periods` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_payroll_detail_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_payroll_detail_contract` FOREIGN KEY (`contract_id`) REFERENCES `contracts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `chk_payroll_detail_values` CHECK (`base_salary` >= 0 AND `salary_coefficient` > 0 AND `allowance` >= 0 AND `standard_days` > 0 AND `actual_days` >= 0 AND `actual_days` <= `standard_days` AND `overtime_pay` >= 0 AND `deduction` >= 0 AND `gross_salary` >= 0 AND `net_salary` >= 0)
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_details');
    }
};
