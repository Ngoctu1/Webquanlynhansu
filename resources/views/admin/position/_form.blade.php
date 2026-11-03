<div class="row">
    <div class="col-md-6 mb-3">
        <label for="name" class="form-label">Tên chức vụ</label>
        <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $position->name ?? '') }}" required maxlength="100">
    </div>
    <div class="col-md-6 mb-3">
        <label for="status" class="form-label">Trạng thái</label>
        <select class="form-select" name="status" id="status">
            <option value="active" @selected((string) old('status', $position->status ?? 'active') === 'active')>Hoạt động</option>
            <option value="inactive" @selected((string) old('status', $position->status ?? 'active') === 'inactive')>Ngừng sử dụng</option>
        </select>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label" for="description">Mô tả</label>
        <textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $position->description ?? '') }}</textarea>
    </div>
</div>