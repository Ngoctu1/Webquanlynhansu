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
CREATE TABLE `contracts` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `contract_code` VARCHAR(30) NOT NULL,
    `employee_id` BIGINT UNSIGNED NOT NULL,
    `previous_contract_id` BIGINT UNSIGNED NULL,
    `contract_type` ENUM('probation','fixed_term','indefinite') NOT NULL,
    `signed_date` DATE NULL,
    `effective_from` DATE NOT NULL,
    `effective_to` DATE NULL,
    `base_salary` DECIMAL(15,2) NOT NULL,
    `salary_coefficient` DECIMAL(6,3) NOT NULL DEFAULT 1.000,
    `allowance` DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    `status` ENUM('draft','pending_approval','approved','rejected') NOT NULL DEFAULT 'draft',
    `created_by` BIGINT UNSIGNED NOT NULL,
    `submitted_by` BIGINT UNSIGNED NULL,
    `submitted_at` DATETIME NULL,
    `approved_by` BIGINT UNSIGNED NULL,
    `approved_at` DATETIME NULL,
    `rejection_reason` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_contracts_code` (`contract_code`),
    KEY `idx_contracts_employee` (`employee_id`),
    KEY `idx_contracts_previous` (`previous_contract_id`),
    KEY `idx_contracts_created_by` (`created_by`),
    KEY `idx_contracts_submitted_by` (`submitted_by`),
    KEY `idx_contracts_approved_by` (`approved_by`),
    KEY `idx_contracts_effectiveness` (`employee_id`, `status`, `effective_from`, `effective_to`),
    CONSTRAINT `fk_contracts_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_contracts_previous` FOREIGN KEY (`previous_contract_id`) REFERENCES `contracts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_contracts_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_contracts_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_contracts_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `chk_contract_dates` CHECK (`effective_to` IS NULL OR `effective_to` >= `effective_from`),
    CONSTRAINT `chk_contract_salary` CHECK (`base_salary` >= 0 AND `salary_coefficient` > 0 AND `allowance` >= 0)
) ENGINE=InnoDB;
SQL);
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
