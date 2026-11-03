<?php

namespace App\Http\Controllers;

use App\Models\{Employee, EmployeeDocument};
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeDocumentController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $actor = $request->user();
        $employee = Employee::visibleTo($actor)->findOrFail($employee->id);
        $this->authorize('view', $employee);
        $this->authorize('create', EmployeeDocument::class);

        $data = $request->validate([
            'doc_type' => ['required', Rule::in(['cccd', 'cv', 'degree', 'other'])],
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $uuidFilename = Str::uuid()->toString() . '.' . $extension;

        // Lưu vào storage/app/private/documents/ (trên disk 'local' có root = storage/app/private)
        $storedPath = $file->storeAs('documents', $uuidFilename, 'local');

        try {
            $document = DB::transaction(function () use ($actor, $employee, $data, $storedPath) {
                $doc = EmployeeDocument::create([
                    'employee_id' => $employee->id,
                    'doc_type' => $data['doc_type'],
                    'title' => $data['title'],
                    'file_path' => $storedPath,
                    'uploaded_by' => $actor->id,
                    'created_at' => now(),
                ]);

                AuditLogger::log('upload_document', 'employee_document', $doc->id, null, [
                    'employee_id' => $employee->id,
                    'doc_type' => $doc->doc_type,
                    'title' => $doc->title,
                ]);

                return $doc;
            });
        } catch (\Throwable $e) {
            if ($storedPath) {
                Storage::disk('local')->delete($storedPath);
            }
            throw $e;
        }

        return back()->with('success', 'Đã tải lên tài liệu đính kèm (' . $document->title . ').');
    }

    public function download(Request $request, EmployeeDocument $document): StreamedResponse
    {
        $document->load('employee.user.role');
        $this->authorize('download', $document);

        abort_unless($document->file_path && Storage::disk('local')->exists($document->file_path), 404, 'Không tìm thấy tệp tài liệu.');

        // Bắt buộc ghi audit_logs khi tải tài liệu nhạy cảm (đặc biệt là CCCD)
        $action = $document->doc_type === 'cccd' ? 'download_sensitive_cccd' : 'download_document';
        AuditLogger::log($action, 'employee_document', $document->id, null, [
            'employee_id' => $document->employee_id,
            'doc_type' => $document->doc_type,
            'title' => $document->title,
        ]);

        $ext = pathinfo($document->file_path, PATHINFO_EXTENSION);
        $downloadName = Str::slug($document->title ?: $document->doc_type) . ($ext ? '.' . $ext : '');

        return Storage::disk('local')->download($document->file_path, $downloadName);
    }

    public function destroy(Request $request, EmployeeDocument $document)
    {
        $document->load('employee.user.role');
        $this->authorize('delete', $document);

        $filePath = $document->file_path;

        DB::transaction(function () use ($document) {
            $old = $document->toArray();
            $document->delete();
            AuditLogger::log('delete_document', 'employee_document', $document->id, $old, null);
        });

        if ($filePath && Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        }

        return back()->with('success', 'Đã xóa tài liệu đính kèm.');
    }
}
