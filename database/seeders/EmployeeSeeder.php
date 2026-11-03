<?php

namespace Database\Seeders;

use App\Models\{Contract, Department, Employee, Position, Role, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Hash};

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // 1. Tài khoản Admin kỹ thuật (không có hồ sơ nhân viên)
            User::updateOrCreate(
                ['email' => 'admin@company.com'],
                [
                    'employee_id' => null,
                    'role_id' => 1, // Admin
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'activation_token' => null,
                    'token_expires_at' => null,
                ]
            );

            $deptBoard = Department::where('name', 'Ban Giám đốc')->firstOrFail();
            $deptHr = Department::where('name', 'Phòng Hành chính - Nhân sự')->firstOrFail();
            $deptIt = Department::where('name', 'Công nghệ thông tin')->firstOrFail();
            $deptSales = Department::where('name', 'Kinh doanh')->firstOrFail();
            $deptMkt = Department::where('name', 'Marketing')->firstOrFail();
            $deptAcc = Department::where('name', 'Kế toán')->firstOrFail();

            $posDirector = Position::where('name', 'Giám đốc')->firstOrFail();
            $posHead = Position::where('name', 'Trưởng phòng')->firstOrFail();
            $posHrStaff = Position::where('name', 'Nhân viên nhân sự')->firstOrFail();
            $posDev = Position::where('name', 'Lập trình viên')->firstOrFail();
            $posTechLead = Position::where('name', 'Trưởng nhóm kỹ thuật')->firstOrFail();
            $posSales = Position::where('name', 'Nhân viên kinh doanh')->firstOrFail();
            $posMkt = Position::where('name', 'Chuyên viên Marketing')->firstOrFail();
            $posAcc = Position::where('name', 'Kế toán viên')->firstOrFail();

            // 2. Hồ sơ & Tài khoản Director (NV001 - director@company.com)
            $directorEmp = Employee::updateOrCreate(
                ['employee_code' => 'NV001'],
                [
                    'full_name' => 'Nguyễn Văn Giám Đốc',
                    'gender' => 'male',
                    'date_of_birth' => '1980-05-15',
                    'citizen_id' => '001080000001',
                    'phone' => '0901000001',
                    'personal_email' => 'director.personal@gmail.com',
                    'work_email' => 'director@company.com',
                    'address' => 'Quận Ba Đình, Hà Nội',
                    'department_id' => $deptBoard->id,
                    'position_id' => $posDirector->id,
                    'hire_date' => '2020-01-01',
                    'employment_status' => 'working',
                    'annual_leave_days' => 12,
                    'approval_status' => 'approved',
                    'approved_at' => now(),
                ]
            );

            $directorUser = User::updateOrCreate(
                ['email' => 'director@company.com'],
                [
                    'employee_id' => $directorEmp->id,
                    'role_id' => 2, // Director
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'activation_token' => null,
                    'token_expires_at' => null,
                ]
            );

            // 3. Hồ sơ, Hợp đồng & Tài khoản HR (NV002 - hr@company.com)
            $hrEmp = Employee::updateOrCreate(
                ['employee_code' => 'NV002'],
                [
                    'full_name' => 'Trần Thị Nhân Sự',
                    'gender' => 'female',
                    'date_of_birth' => '1990-08-20',
                    'citizen_id' => '001090000002',
                    'phone' => '0901000002',
                    'personal_email' => 'hr.personal@gmail.com',
                    'work_email' => 'hr@company.com',
                    'address' => 'Quận Cầu Giấy, Hà Nội',
                    'department_id' => $deptHr->id,
                    'position_id' => $posHead->id,
                    'hire_date' => '2021-03-01',
                    'employment_status' => 'working',
                    'annual_leave_days' => 12,
                    'approval_status' => 'approved',
                    'approved_by' => $directorUser->id,
                    'approved_at' => now(),
                ]
            );

            User::updateOrCreate(
                ['email' => 'hr@company.com'],
                [
                    'employee_id' => $hrEmp->id,
                    'role_id' => 3, // HR
                    'password' => Hash::make('password'),
                    'status' => 'active',
                    'activation_token' => null,
                    'token_expires_at' => null,
                ]
            );

            Contract::updateOrCreate(
                ['contract_code' => 'HD001'],
                [
                    'employee_id' => $hrEmp->id,
                    'previous_contract_id' => null,
                    'contract_type' => 'indefinite',
                    'signed_date' => '2021-02-25',
                    'effective_from' => '2021-03-01',
                    'effective_to' => null,
                    'base_salary' => 22000000,
                    'coefficient' => 1.20,
                    'allowance' => 2500000,
                    'allowance_note' => 'Phụ cấp trách nhiệm, ăn trưa và xăng xe',
                    'file_path' => 'contracts/HD001.pdf',
                    'status' => 'active',
                    'approval_status' => 'approved',
                    'approved_by' => $directorUser->id,
                    'approved_at' => now(),
                ]
            );

            // 4. Nhóm 1 - Nhân viên đang làm việc (working) - 5 nhân viên
            $workingEmployees = [
                [
                    'code' => 'NV003',
                    'contract_code' => 'HD002',
                    'full_name' => 'Nguyễn Minh Anh',
                    'gender' => 'female',
                    'dob' => '1996-02-14',
                    'citizen_id' => '001096000003',
                    'phone' => '0905000003',
                    'personal_email' => 'minhanh.personal@gmail.com',
                    'work_email' => 'minhanh@company.com',
                    'address' => 'Quận Ba Đình, Hà Nội',
                    'department_id' => $deptHr->id,
                    'position_id' => $posHrStaff->id,
                    'hire_date' => '2023-01-15',
                    'base_salary' => 14000000,
                    'allowance' => 1000000,
                ],
                [
                    'code' => 'NV004',
                    'contract_code' => 'HD003',
                    'full_name' => 'Trần Quốc Bảo',
                    'gender' => 'male',
                    'dob' => '1995-06-10',
                    'citizen_id' => '001095000004',
                    'phone' => '0905000004',
                    'personal_email' => 'quocbao.personal@gmail.com',
                    'work_email' => 'quocbao@company.com',
                    'address' => 'Quận Cầu Giấy, Hà Nội',
                    'department_id' => $deptIt->id,
                    'position_id' => $posDev->id,
                    'hire_date' => '2022-07-01',
                    'base_salary' => 18000000,
                    'allowance' => 1500000,
                ],
                [
                    'code' => 'NV005',
                    'contract_code' => 'HD004',
                    'full_name' => 'Lê Thu Cẩm',
                    'gender' => 'female',
                    'dob' => '1997-11-22',
                    'citizen_id' => '001097000005',
                    'phone' => '0905000005',
                    'personal_email' => 'thucam.personal@gmail.com',
                    'work_email' => 'thucam@company.com',
                    'address' => 'Quận Hải Châu, Đà Nẵng',
                    'department_id' => $deptSales->id,
                    'position_id' => $posSales->id,
                    'hire_date' => '2023-04-01',
                    'base_salary' => 15000000,
                    'allowance' => 1200000,
                ],
                [
                    'code' => 'NV006',
                    'contract_code' => 'HD005',
                    'full_name' => 'Phạm Đức Duy',
                    'gender' => 'male',
                    'dob' => '1994-09-05',
                    'citizen_id' => '001094000006',
                    'phone' => '0905000006',
                    'personal_email' => 'ducduy.personal@gmail.com',
                    'work_email' => 'ducduy@company.com',
                    'address' => 'Quận 3, TP. Hồ Chí Minh',
                    'department_id' => $deptMkt->id,
                    'position_id' => $posMkt->id,
                    'hire_date' => '2022-10-01',
                    'base_salary' => 16000000,
                    'allowance' => 1000000,
                ],
                [
                    'code' => 'NV007',
                    'contract_code' => 'HD006',
                    'full_name' => 'Đỗ Gia Hân',
                    'gender' => 'female',
                    'dob' => '1993-12-18',
                    'citizen_id' => '001093000007',
                    'phone' => '0905000007',
                    'personal_email' => 'giahan.personal@gmail.com',
                    'work_email' => 'giahan@company.com',
                    'address' => 'Quận Thanh Xuân, Hà Nội',
                    'department_id' => $deptIt->id,
                    'position_id' => $posTechLead->id,
                    'hire_date' => '2021-09-01',
                    'base_salary' => 25000000,
                    'allowance' => 2000000,
                ],
            ];

            foreach ($workingEmployees as $item) {
                $emp = Employee::updateOrCreate(
                    ['employee_code' => $item['code']],
                    [
                        'full_name' => $item['full_name'],
                        'gender' => $item['gender'],
                        'date_of_birth' => $item['dob'],
                        'citizen_id' => $item['citizen_id'],
                        'phone' => $item['phone'],
                        'personal_email' => $item['personal_email'],
                        'work_email' => $item['work_email'],
                        'address' => $item['address'],
                        'department_id' => $item['department_id'],
                        'position_id' => $item['position_id'],
                        'hire_date' => $item['hire_date'],
                        'employment_status' => 'working',
                        'annual_leave_days' => 12,
                        'approval_status' => 'approved',
                        'approved_by' => $directorUser->id,
                        'approved_at' => now(),
                    ]
                );

                User::updateOrCreate(
                    ['email' => $item['work_email']],
                    [
                        'employee_id' => $emp->id,
                        'role_id' => 4, // Employee
                        'password' => Hash::make('password'),
                        'status' => 'active',
                        'activation_token' => null,
                        'token_expires_at' => null,
                    ]
                );

                Contract::updateOrCreate(
                    ['contract_code' => $item['contract_code']],
                    [
                        'employee_id' => $emp->id,
                        'previous_contract_id' => null,
                        'contract_type' => 'fixed_term',
                        'signed_date' => $item['hire_date'],
                        'effective_from' => $item['hire_date'],
                        'effective_to' => now()->addYear()->toDateString(),
                        'base_salary' => $item['base_salary'],
                        'coefficient' => 1.00,
                        'allowance' => $item['allowance'],
                        'allowance_note' => 'Phụ cấp ăn trưa, xăng xe và điện thoại',
                        'file_path' => 'contracts/' . $item['contract_code'] . '.pdf',
                        'status' => 'active',
                        'approval_status' => 'approved',
                        'approved_by' => $directorUser->id,
                        'approved_at' => now(),
                    ]
                );
            }

            // 5. Nhóm 2 - Nhân viên mới chờ Giám đốc duyệt (pending) - 2 nhân viên
            $pendingEmployees = [
                [
                    'code' => 'NV008',
                    'full_name' => 'Cao Bảo Yến',
                    'gender' => 'female',
                    'dob' => '1999-04-12',
                    'citizen_id' => '001099000008',
                    'phone' => '0905000008',
                    'personal_email' => 'baoyen.personal@gmail.com',
                    'work_email' => 'baoyen@company.com',
                    'address' => 'Quận Tây Hồ, Hà Nội',
                    'department_id' => $deptIt->id,
                    'position_id' => $posDev->id,
                    'hire_date' => now()->toDateString(),
                    'token' => hash('sha256', 'sample-activation-token-nv008'),
                ],
                [
                    'code' => 'NV009',
                    'full_name' => 'Hồ Minh Phúc',
                    'gender' => 'male',
                    'dob' => '1998-07-25',
                    'citizen_id' => '001098000009',
                    'phone' => '0905000009',
                    'personal_email' => 'minhphuc.personal@gmail.com',
                    'work_email' => 'minhphuc@company.com',
                    'address' => 'Quận Ngũ Hành Sơn, Đà Nẵng',
                    'department_id' => $deptSales->id,
                    'position_id' => $posSales->id,
                    'hire_date' => now()->toDateString(),
                    'token' => hash('sha256', 'sample-activation-token-nv009'),
                ],
            ];

            foreach ($pendingEmployees as $item) {
                $emp = Employee::updateOrCreate(
                    ['employee_code' => $item['code']],
                    [
                        'full_name' => $item['full_name'],
                        'gender' => $item['gender'],
                        'date_of_birth' => $item['dob'],
                        'citizen_id' => $item['citizen_id'],
                        'phone' => $item['phone'],
                        'personal_email' => $item['personal_email'],
                        'work_email' => $item['work_email'],
                        'address' => $item['address'],
                        'department_id' => $item['department_id'],
                        'position_id' => $item['position_id'],
                        'hire_date' => $item['hire_date'],
                        'employment_status' => 'pending',
                        'annual_leave_days' => 12,
                        'approval_status' => 'pending_approval',
                        'approved_by' => null,
                        'approved_at' => null,
                    ]
                );

                User::updateOrCreate(
                    ['email' => $item['work_email']],
                    [
                        'employee_id' => $emp->id,
                        'role_id' => 4, // Employee
                        'password' => null,
                        'status' => 'pending_reset',
                        'activation_token' => $item['token'],
                        'token_expires_at' => now()->addDays(7),
                    ]
                );
            }

            // 6. Nhóm 3 - Nhân viên đã nghỉ việc (resigned) - 1 nhân viên
            $resignedEmp = Employee::updateOrCreate(
                ['employee_code' => 'NV010'],
                [
                    'full_name' => 'Vũ Ngọc Em',
                    'gender' => 'female',
                    'date_of_birth' => '1995-03-30',
                    'citizen_id' => '001095000010',
                    'phone' => '0905000010',
                    'personal_email' => 'ngocem.personal@gmail.com',
                    'work_email' => 'ngocem@company.com',
                    'address' => 'Quận Ninh Kiều, Cần Thơ',
                    'department_id' => $deptAcc->id,
                    'position_id' => $posAcc->id,
                    'hire_date' => '2021-06-01',
                    'employment_status' => 'resigned',
                    'annual_leave_days' => 12,
                    'resignation_date' => '2025-12-31',
                    'resignation_reason' => 'Chuyển nơi cư trú theo gia đình',
                    'approval_status' => 'approved',
                    'approved_by' => $directorUser->id,
                    'approved_at' => '2021-06-01 09:00:00',
                ]
            );

            User::updateOrCreate(
                ['email' => 'ngocem@company.com'],
                [
                    'employee_id' => $resignedEmp->id,
                    'role_id' => 4, // Employee
                    'password' => Hash::make('password'),
                    'status' => 'locked',
                    'activation_token' => null,
                    'token_expires_at' => null,
                ]
            );

            Contract::updateOrCreate(
                ['contract_code' => 'HD007'],
                [
                    'employee_id' => $resignedEmp->id,
                    'previous_contract_id' => null,
                    'contract_type' => 'fixed_term',
                    'signed_date' => '2021-05-25',
                    'effective_from' => '2021-06-01',
                    'effective_to' => '2025-12-31',
                    'base_salary' => 16000000,
                    'coefficient' => 1.00,
                    'allowance' => 800000,
                    'allowance_note' => 'Phụ cấp ăn trưa và xăng xe',
                    'file_path' => 'contracts/HD007.pdf',
                    'status' => 'terminated',
                    'approval_status' => 'approved',
                    'approved_by' => $directorUser->id,
                    'approved_at' => '2021-06-01 09:00:00',
                ]
            );
        });
    }
}
