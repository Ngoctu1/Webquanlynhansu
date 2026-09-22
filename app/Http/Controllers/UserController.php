<?php

namespace App\Http\Controllers;

use App\Mail\AccountInvitationMail;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Employee $employee)
    {
        abort_unless($request->user()?->status === 'active' &&
            in_array((int) $request->user()->role_id, [1, 2], true), 403);

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:50', 'alpha_dash:ascii', 'unique:users,username'],
            'password' => ['prohibited'],
            'password_confirmation' => ['prohibited'],
            'role_id' => ['prohibited'],
        ]);

        try {
            DB::transaction(function () use ($request, $employee, $validated) {
                // Khóa nhân viên để hai yêu cầu không đồng thời tạo hai tài khoản.
                $employee = Employee::whereKey($employee->id)->lockForUpdate()->firstOrFail();
                if ($employee->user()->exists()) {
                    throw ValidationException::withMessages(['account' => 'Nhân viên đã có tài khoản.']);
                }

                $user = User::create([
                    'employee_id' => $employee->id,
                    'role_id' => 3,
                    'username' => $validated['username'],
                    'password' => null,
                    'status' => 'pending',
                ]);
                $token = Str::random(64);
                $invitation = $user->invitations()->create([
                    'token_hash' => hash('sha256', $token),
                    'expires_at' => now()->addHours(24),
                    'created_by' => $request->user()->id,
                ]);

                foreach ([$user, $invitation] as $entity) {
                    $fields = $entity instanceof User
                        ? ['employee_id', 'role_id', 'username', 'status']
                        : ['user_id', 'expires_at', 'created_by'];
                    AuditLog::create([
                        'actor_user_id' => $request->user()->id,
                        'action' => 'CREATE',
                        'entity_type' => get_class($entity),
                        'entity_id' => $entity->id,
                        'old_values' => null,
                        'new_values' => $entity->only($fields),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'created_at' => now(),
                    ]);
                }

                try {
                    // Gửi đồng bộ: lỗi gửi mail sẽ rollback toàn bộ transaction.
                    Mail::to($employee->email)->send(new AccountInvitationMail(
                        $employee->full_name,
                        $user->username,
                        route('account.activate', ['token' => $token]),
                        $invitation->expires_at->format('d/m/Y H:i T'),
                    ));
                } catch (\Throwable $exception) {
                    // Không log exception mail vì có thể chứa toàn bộ link/token.
                    throw ValidationException::withMessages([
                        'account' => 'Không gửi được email kích hoạt. Tài khoản chưa được tạo; vui lòng thử lại hoặc kiểm tra cấu hình mail.',
                    ]);
                }
            });
        } catch (QueryException $exception) {
            throw ValidationException::withMessages([
                'account' => 'Không thể tạo tài khoản. Vui lòng kiểm tra tên đăng nhập, role Employee và thử lại.',
            ]);
        }

        return redirect()->route('employees.show', $employee)
            ->with('success', 'Đã tạo tài khoản chờ kích hoạt và gửi email tới nhân viên.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
