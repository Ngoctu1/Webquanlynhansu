# Schema HRM phát triển — 8 bảng nghiệp vụ

Bộ migration mô tả trực tiếp cấu trúc cuối cùng cho database phát triển có thể tạo lại từ Seeder.

## Bảng nghiệp vụ

1. `roles` — Admin, Director, HR và Employee.
2. `departments`.
3. `positions`.
4. `employees` — thông tin nhân sự, phòng ban, chức vụ và `profile_photo_path`.
5. `users` — tài khoản, vai trò, trạng thái kích hoạt.
6. `contracts` — hợp đồng nội bộ và `signed_document_path` cho bản đã ký.
7. `leave_requests` — yêu cầu nghỉ phép, trạng thái duyệt và thông tin người duyệt.
8. `audit_logs` — nhật ký nghiệp vụ.

Các bảng hạ tầng Laravel (`cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `sessions`) vẫn được giữ. Các module chấm công và bảng lương đã được loại bỏ hoàn toàn; do đó không còn bảng, khóa ngoại hay migration tương ứng.

## Ràng buộc chính

- Hợp đồng và hồ sơ nhân viên lưu **đường dẫn Storage**, không lưu dữ liệu tệp hay URL công khai trong database.
- `leave_requests` có kiểm tra khoảng ngày hợp lệ, trạng thái duyệt và người duyệt ở cấp database; Controller còn kiểm tra chồng lấn với yêu cầu `pending`/`approved`.
- Database dùng MySQL/MariaDB do các migration tạo bảng sử dụng cú pháp SQL của hệ này.

## Tạo lại database phát triển

Chỉ chạy sau khi xác nhận môi trường là `local` và tên database đúng database phát triển:

```powershell
php artisan migrate:fresh --seed
```

Lệnh xóa toàn bộ dữ liệu trong database đang kết nối. Không dùng để nâng cấp database cần bảo toàn dữ liệu.
