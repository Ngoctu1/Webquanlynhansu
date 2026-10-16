<?php

namespace App\Http\Controllers;

use App\Http\Requests\{FilterCatalogRequest, SavePositionRequest, UpdateCatalogStatusRequest};

use App\Models\Position;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    public function index(FilterCatalogRequest $request)
    {
        $filters = $request->validated();
        $positions = Position::withCount('employees')
            ->when(isset($filters['q']), fn ($q) => $q->where(fn ($q) => $q->where('code', 'like', '%'.$filters['q'].'%')->orWhere('name', 'like', '%'.$filters['q'].'%')->orWhere('description', 'like', '%'.$filters['q'].'%')))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('code')->paginate(15)->withQueryString();
        return view('admin.position.index', compact('positions', 'filters'));
    }

    public function create() { return view('admin.position.create'); }

    public function store(SavePositionRequest $request)
    {
        $data = $request->validated();
        $position = DB::transaction(function () use ($data) {
            $model = Position::create($data);
            AuditService::record('POSITION_CREATED', $model);
            return $model;
        });
        return redirect()->route('positions.show', $position)->with('success', 'Đã tạo chức vụ.');
    }

    public function show(Position $position)
    {
        $position->loadCount('employees');
        return view('admin.position.show', compact('position'));
    }

    public function edit(Position $position) { return view('admin.position.edit', compact('position')); }

    public function update(SavePositionRequest $request, Position $position)
    {
        $data = $request->validated();
        $this->save($position, $data);
        return redirect()->route('positions.show', $position)->with('success', 'Đã cập nhật.');
    }

    public function destroy(Position $position)
    {
        $this->save($position, ['status' => 'inactive']);
        return redirect()->route('positions.index')->with('success', 'Đã ngừng sử dụng; dữ liệu tham chiếu được giữ nguyên.');
    }

    public function updateStatus(UpdateCatalogStatusRequest $request, Position $position)
    {
        $this->save($position, $request->validated());
        return back()->with('success', 'Đã cập nhật trạng thái.');
    }

    private function save(Position $model, array $data): void
    {
        DB::transaction(function () use ($model, $data) {
            $model = Position::lockForUpdate()->findOrFail($model->id);
            $old = $model->getAttributes();
            $model->fill($data);
            if ($model->isDirty()) {
                $model->save();
                AuditService::record('POSITION_UPDATED', $model, $old);
            }
        });
    }

}
