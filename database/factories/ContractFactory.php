<?php

namespace Database\Factories;

use App\Models\{Contract, Employee};
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        return [
            'contract_code' => 'HD' . fake()->unique()->numerify('###'),
            'employee_id' => Employee::factory(),
            'previous_contract_id' => null,
            'contract_type' => 'fixed_term',
            'signed_date' => today()->subDays(7),
            'effective_from' => today(),
            'effective_to' => today()->addYears(2)->subDay(),
            'base_salary' => 12000000,
            'coefficient' => 1.00,
            'allowance' => 500000,
            'allowance_note' => 'Phụ cấp ăn trưa và xăng xe',
            'file_path' => null,
            'status' => 'active',
            'approval_status' => 'pending_approval',
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ];
    }
}
