<?php

namespace App\Services;

use App\Models\{Contract, Employee};
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordCodeService
{
    public function employee(Closure $store): Employee
    {
        return $this->create(Employee::class, 'employee_code', 'NV', 'uq_employees_code', $store);
    }

    public function contract(Closure $store): Contract
    {
        return $this->create(Contract::class, 'contract_code', 'HD', 'uq_contracts_code', $store);
    }

    private function create(string $model, string $column, string $prefix, string $uniqueKey, Closure $store): Model
    {
        // Keep allocation, insertion and the caller's audit in one transaction.
        for ($attempt = 0; $attempt < 5; $attempt++) {
            try {
                return DB::transaction(function () use ($model, $column, $prefix, $store) {
                    // A locking read sees current values even after a competing insert.
                    $codes = $model::where($column, 'like', $prefix.'%')->orderBy($column)
                        ->lockForUpdate()->pluck($column)->all();
                    $used = array_fill_keys(array_map(fn ($code) => strtoupper(rtrim($code)), $codes), true);
                    for ($number = 1; $number <= 999; $number++) {
                        $code = $prefix.str_pad((string) $number, 3, '0', STR_PAD_LEFT);
                        if (!isset($used[$code])) { return $store($code); }
                    }
                    throw ValidationException::withMessages([
                        $column => 'Đã sử dụng hết mã '.$prefix.'001–'.$prefix.'999. Không thể tạo thêm bản ghi.',
                    ]);
                }, 3);
            } catch (QueryException $exception) {
                // The unique index is the final guard against writers outside this service.
                if ((int) ($exception->errorInfo[1] ?? 0) !== 1062
                    || !str_contains($exception->getMessage(), $uniqueKey)) {
                    throw $exception;
                }
            }
        }

        throw ValidationException::withMessages([$column => 'Đang có thao tác tạo đồng thời. Vui lòng thử lại.']);
    }
}
