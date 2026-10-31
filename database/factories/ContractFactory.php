<?php

namespace Database\Factories;

use App\Models\{Contract, Employee, User};
use Illuminate\Database\Eloquent\Factories\Factory;

class ContractFactory extends Factory
{
    protected $model = Contract::class;

    public function definition(): array
    {
        return [
            'contract_code' => 'HD'.fake()->unique()->numerify('###'),
            'employee_id' => Employee::factory(),
            'previous_contract_id' => null,
            'contract_type' => 'fixed_term',
            'signed_date' => today()->subDays(7),
            'effective_from' => today(),
            'effective_to' => today()->addYears(2)->subDay(),
            'base_salary' => 12000000,
            'salary_coefficient' => 1.000,
            'allowance' => 500000,
            'status' => 'draft',
            'created_by' => User::factory(),
            'submitted_by' => null,
            'submitted_at' => null,
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
            'signed_document_path' => null,
        ];
    }
}
