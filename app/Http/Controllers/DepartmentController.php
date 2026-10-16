<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FilterCatalogRequest, SaveDepartmentRequest, UpdateCatalogStatusRequest};

use App\Models\Department;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function index(FilterCatalogRequest $request)
    {
        $filters = $request->validated();
        $departments = Department::withCount('employees')
            ->when(isset($filters['q']), fn ($q) => $q->where(fn ($q) => $q->where('code', 'like', '%'.$filters['q'].'%')->orWhere('name', 'like', '%'.$filters['q'].'%')->orWhere('description', 'like', '%'.$filters['q'].'%')))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('code')->paginate(15)->withQueryString();
        return view('admin.department.index', compact('departments', 'filters'));
    }

    public function create() { return view('admin.department.create'); }

    public function store(SaveDepartmentRequest $request)
    {
        $data = $request->validated();
        $department = DB::transaction(function () use ($data) {
            $model = Department::create($data);
            AuditService::record('DEPARTMENT_CREATED', $model);
            return $model;
        });
        return redirect()->route('departments.show', $department)->with('success', 'Đã tạo phòng ban.');
    }

    public function show(Department $department)
    {
        $department->loadCount('employees');
        return view('admin.department.show', compact('department'));
    }

    public function edit(Department $department) { return view('admin.department.edit', compact('department')); }

    public function update(SaveDepartmentRequest $request, Department $department)
    {
        $data = $request->validated();
        $this->save($department, $data);
        return redirect()->route('departments.show', $department)->with('success', 'Đã cập nhật.');
    }

    public function destroy(Department $department)
    {
        $this->save($department, ['status' => 'inactive']);
        return redirect()->route('departments.index')->with('success', 'Đã ngừng sử dụng; dữ liệu tham chiếu được giữ nguyên.');
    }

    public function updateStatus(UpdateCatalogStatusRequest $request, Department $department)
    {
        $this->save($department, $request->validated());
        return back()->with('success', 'Đã cập nhật trạng thái.');
    }

    private function save(Department $model, array $data): void
    {
        DB::transaction(function () use ($model, $data) {
            $model = Department::lockForUpdate()->findOrFail($model->id);
            $old = $model->getAttributes();
            $model->fill($data);
            if ($model->isDirty()) {
                $model->save();
                AuditService::record('DEPARTMENT_UPDATED', $model, $old);
            }
        });
    }

}
