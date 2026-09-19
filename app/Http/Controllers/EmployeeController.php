<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Hiển thị danh sách nhân viên (có tìm kiếm & lọc).
     */
    public function index(Request $request)
    {
        // Lấy tất cả nhân viên — dùng query builder để hỗ trợ tìm kiếm/lọc
        $query = Employee::query();

        // Tìm kiếm theo tên hoặc email hoặc mã nhân viên
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        // Lọc theo phòng ban
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Lọc theo chức vụ
        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Phân trang — 15 bản ghi mỗi trang
        $employees   = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();
        $departments = Department::all();
        $positions   = Position::all();

        return view('admin.employee.index', compact('employees', 'departments', 'positions'));
    }

    /**
     * Hiển thị form tạo nhân viên mới.
     */
    public function create()
    {
        $departments = Department::all();
        $positions   = Position::all();

        return view('admin.employee.create', compact('departments', 'positions'));
    }

    /**
     * Lưu nhân viên mới vào database.
     */
    public function store(Request $request)
    {
        // Validation
        
        $validated = $request->validate([
            'employee_code'   => 'required|string|max:20|unique:employees,employee_code',
            'full_name'         => 'required|string|max:100',
            'date_of_birth'      => 'nullable|date',
            'gender'      => 'nullable|in:Nam,Nữ,Khác',
            'phone'  => 'nullable|string|max:15',
            'email'          => 'nullable|email|max:100|unique:employees,email',
            'address'        => 'nullable|string|max:255',
            'cccd'           => 'nullable|string|max:20',
            'hire_date'   => 'nullable|date',
            'department_id'  => 'nullable|exists:departments,id',
            'position_id'    => 'nullable|exists:positions,id',
            'status'     => 'required|in:Đang làm việc,Nghỉ phép,Đã nghỉ việc,Thử việc',
        ], [
            'employee_code.required'  => 'Mã nhân viên không được để trống.',
            'employee_code.unique'    => 'Mã nhân viên đã tồn tại.',
            'full_name.required'        => 'Họ và tên không được để trống.',
            'email.email'            => 'Email không đúng định dạng.',
            'email.unique'           => 'Email đã được sử dụng.',
            'status.required'    => 'Vui lòng chọn trạng thái.',
        ]);

        // Tạo nhân viên — dùng new + fill + save để tránh lỗi fillable chưa khai báo
        $employee = new Employee();
        $employee->employee_code  = $validated['employee_code'];
        $employee->full_name        = $validated['full_name'];
        $employee->date_of_birth     = $validated['date_of_birth']     ?? null;
        $employee->gender     = $validated['gender']     ?? null;
        $employee->phone = $validated['phone'] ?? null;
        $employee->email         = $validated['email']         ?? null;
        $employee->address       = $validated['address']       ?? null;
        $employee->cccd          = $validated['cccd']          ?? null;
        $employee->hire_date  = $validated['hire_date']  ?? null;
        $employee->department_id = $validated['department_id'] ?? null;
        $employee->position_id   = $validated['position_id']   ?? null;
        $employee->status    = $validated['status'];
        $employee->save();

        return redirect()->route('employees.index')
                         ->with('success', 'Thêm nhân viên thành công!');
    }

    /**
     * Hiển thị chi tiết một nhân viên.
     */
    public function show(Employee $employee)
    {
        // Load thêm thông tin phòng ban và chức vụ nếu có
        $department = $employee->department_id
            ? Department::find($employee->department_id)
            : null;

        $position = $employee->position_id
            ? Position::find($employee->position_id)
            : null;

        return view('admin.employee.show', compact('employee', 'department', 'position'));
    }

    /**
     * Hiển thị form chỉnh sửa nhân viên.
     */
    public function edit(Employee $employee)
    {
        $departments = Department::all();
        $positions   = Position::all();

        return view('admin.employee.edit', compact('employee', 'departments', 'positions'));
    }

    /**
     * Cập nhật thông tin nhân viên.
     */
    public function update(Request $request, Employee $employee)
    {
        // Validation — bỏ unique nếu trùng với chính bản ghi hiện tại
        $validated = $request->validate([
            'employee_code'   => 'required|string|max:20|unique:employees,employee_code,' . $employee->id,
            'full_name'         => 'required|string|max:100',
            'date_of_birth'      => 'nullable|date',
            'gender'      => 'nullable|in:Nam,Nữ,Khác',
            'phone'  => 'nullable|string|max:15',
            'email'          => 'nullable|email|max:100|unique:employees,email,' . $employee->id,
            'address'        => 'nullable|string|max:255',
            'cccd'           => 'nullable|string|max:20',
            'hire_date'   => 'nullable|date',
            'department_id'  => 'nullable|exists:departments,id',
            'position_id'    => 'nullable|exists:positions,id',
            'status'     => 'required|in:Đang làm việc,Nghỉ phép,Đã nghỉ việc,Thử việc',
        ], [
            'employee_code.required'  => 'Mã nhân viên không được để trống.',
            'employee_code.unique'    => 'Mã nhân viên đã tồn tại.',
            'full_name.required'        => 'Họ và tên không được để trống.',
            'email.email'            => 'Email không đúng định dạng.',
            'email.unique'           => 'Email đã được sử dụng.',
            'status.required'    => 'Vui lòng chọn trạng thái.',
        ]);

        // Cập nhật — gán từng trường để không cần fillable
        $employee->employee_code  = $validated['employee_code'];
        $employee->full_name        = $validated['full_name'];
        $employee->date_of_birth     = $validated['date_of_birth']     ?? null;
        $employee->gender     = $validated['gender']     ?? null;
        $employee->phone = $validated['phone'] ?? null;
        $employee->email         = $validated['email']         ?? null;
        $employee->address       = $validated['address']       ?? null;
        $employee->cccd          = $validated['cccd']          ?? null;
        $employee->hire_date  = $validated['hire_date']  ?? null;
        $employee->department_id = $validated['department_id'] ?? null;
        $employee->position_id   = $validated['position_id']   ?? null;
        $employee->status    = $validated['status'];
        $employee->save();

        return redirect()->route('employees.index')
                         ->with('success', 'Cập nhật nhân viên thành công!');
    }

    /**
     * Xóa nhân viên khỏi database.
     */
    public function destroy(Employee $employee)
    {
        
        $employee->delete();

        return redirect()->route('employees.index')
                         ->with('success', 'Đã xóa nhân viên thành công!');
    }
}
