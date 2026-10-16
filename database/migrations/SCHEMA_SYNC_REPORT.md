# Báo cáo đồng bộ HRM — 16/10/2026

Mã nguồn đã chuyển từ schema cũ sang SQL 10 bảng, cộng phần mở rộng lịch làm việc/OT mà bạn đã chốt trong hội thoại: **11 bảng nghiệp vụ**. Đây là thay đổi mã nguồn và migration nền cho database mới; **chưa chuyển đổi, reset hoặc chạy seeder trên database hiện tại**.

## Cập nhật tài khoản Admin và Form Request

- AdminUserSeeder dùng email admin@hrm.com và mật khẩu cố định do người dùng yêu cầu trong hội thoại. Mật khẩu được hash khi lưu; tài khoản active, có activated_at, hồ sơ working. Chạy lại giữ ID, khôi phục mật khẩu/trạng thái và xóa token cũ. Không tự nâng quyền khi email thuộc tài khoản có vai trò khác.
- DatabaseSeeder gọi AdminUserSeeder sau BootstrapUserSeeder để tạo đủ bốn tài khoản mẫu. BootstrapUserSeeder tra hồ sơ nhân viên 1 theo tài khoản/email và cấp mã NV còn trống khi tạo mới, tránh chiếm NV001 nếu Admin đã được tạo trước.
- Đã tách validation của 11 controller thành 21 class Form Request tại app/Http/Requests. Không tạo Resources theo đính chính của người dùng. Danh sách và cách sử dụng nằm trong [README Form Request](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Requests/README.md).
- Controller dùng validated()/payload(); Request xử lý dữ liệu đầu vào và quyền phù hợp. Service/transaction vẫn giữ kiểm tra nghiệp vụ, khóa dữ liệu và audit. Route, View và schema không thay đổi trong lượt refactor này.
- Thêm AdminUserSeederTest, bổ sung HrmWorkflowTest cho token hết hạn trước validation và dữ liệu mật khẩu sai kiểu. 37 file PHP đã qua php -l; chưa chạy feature test hoặc seeder vì không nạp vendor/.env.

## Cập nhật sinh mã và quyền Admin/Director

- Form tạo nhân viên và hợp đồng không còn ô nhập mã. Backend chọn mã còn trống nhỏ nhất trong NV001–NV999 và HD001–HD999; không nhận mã tùy ý từ request tạo mới. Mã được giữ nguyên khi sửa; mã cũ ngoài dải không bị đổi hồi tố.
- RecordCodeService khóa dữ liệu mã khi đọc, tạo bản ghi và audit trong cùng transaction. Unique index vẫn bảo vệ dữ liệu; có retry khi deadlock/trùng mã do thao tác đồng thời. Hết 999 mã thì báo lỗi validation, không sinh NV1000/HD1000.
- HR được quản lý nhân sự, tạo/trình hợp đồng và bảng lương, không phê duyệt. Director có toàn bộ nghiệp vụ HR và được phê duyệt/từ chối. Admin có các quyền trên, thêm đổi vai trò và khóa/mở khóa tài khoản các vai trò khác. Employee chỉ truy cập vùng cá nhân.
- Admin không tự khóa/đổi vai trò tài khoản đang sử dụng. Thay đổi vai trò được kiểm tra phía server và ghi USER_ROLE_UPDATED vào Audit Log. Các giới hạn trạng thái hợp đồng/lương vẫn áp dụng cho mọi vai trò.
- Migration mới `2026_10_16_000001_add_admin_role.php` mở rộng enum roles.name bằng Admin; không đổi ID các role hiện có. RoleSeeder bổ sung Admin sau ba role hiện tại. Rollback bị chặn khi còn User giữ role Admin.
- Chạy `php artisan migrate`, sau đó `php artisan db:seed --class=RoleSeeder`. Tạo/cập nhật tài khoản Admin bằng `php artisan db:seed --class=AdminUserSeeder` (không cần nhập tại terminal nữa). Seeder tạo hồ sơ working với mã NV tự sinh và tài khoản active; không tự nâng quyền email đang thuộc vai trò khác.
- File mới: app/Services/RecordCodeService.php, database/migrations/2026_10_16_000001_add_admin_role.php, database/seeders/AdminUserSeeder.php, tests/Feature/RecordCodeTest.php.
- File cập nhật: EmployeeController, ContractController, AuthController, UserController; ContractService, AccountInvitationService, AttendanceService, PayrollService; Role/User; bootstrap/app.php; routes/web.php; RoleSeeder; các form nhân viên/hợp đồng và 15 Blade có kiểm tra quyền; HrmWorkflowTest.
- Đã bổ sung kiểm thử sinh mã, chặn sửa/giả mạo mã, hết dải mã, rollback, Director làm nghiệp vụ HR, Admin đăng nhập và quản lý quyền, HR bị chặn duyệt. PHP lint đạt; chưa thực thi feature test, migration hoặc seeder, không nạp vendor/.env.

## Database và các điểm khác SQL đính kèm đã được chốt thêm

Nguồn đã đọc: [hrm_schema_10_tables.sql](C:/Users/Admin/Downloads/hrm_schema_10_tables.sql).
Các bảng: roles, departments, positions, work_schedules, employees, users, contracts, attendances, payroll_periods, payroll_details, audit_logs.

| Thành phần | Kết quả |
|---|---|
| Migration gốc | DDL MySQL/MariaDB giữ kiểu dữ liệu, enum, unique/index có tên, FK RESTRICT/CASCADE/SET NULL, CHECK và timestamp mặc định theo SQL. Không có CREATE DATABASE trong migration. |
| work_schedules | Bảng thứ 11: giờ ca, nghỉ giữa ca, phút chuẩn, phút cho phép muộn, ngày làm việc, trạng thái. Seed ca 08:00–17:00, nghỉ 12:00–13:00, T2–T6, 480 phút, cho phép 5 phút. |
| employees | Thêm work_schedule_id bắt buộc, FK RESTRICT/CASCADE. Dùng personal_email/work_email/employment_status; không SoftDeletes. |
| attendances | Thêm regular_minutes, overtime_minutes/type/status/approved_by. work_units DECIMAL(6,4). |
| payroll_details | actual_days DECIMAL(7,4), thêm calculation_snapshot JSON để giữ lịch, phút công, căn cứ OT, người xác nhận và kết quả từng ngày. |
| Bảng cũ | Không còn position_salaries, employee_salaries, employee_assignments, account_invitations. |
| Hạ tầng Laravel | Giữ cache/cache_locks, jobs/job_batches/failed_jobs; bổ sung sessions. Không tạo password_reset_tokens vì chưa có luồng quên mật khẩu. |

Mỗi bảng chỉ có một migration tạo bảng; kiểm tra tĩnh 20 FK đều trỏ tới bảng đã được tạo trước hoặc chính bảng đó. Không đọc cấu hình kết nối/.env nên chưa xác nhận database thực tế có tên hrm_laravel hay không.

## Model, controller và nghiệp vụ

- Đồng bộ 11 Model, fillable/casts/hidden và relationship. User giữ Laravel Authentication; AuditLog dùng user_id và auditable_type/id, có morphTo.
- AuthController, RoleMiddleware và guest redirect dùng roles.name HR/Director/Employee/Admin. Login email + password, chỉ active, regenerate session; logout invalidate session. HR/Director/Admin → /admin, Employee → /user. Các vai trò đều có vùng cá nhân.
- Department/Position: code unique, name tối đa 120 và được trùng; tìm mã/tên/mô tả, lọc, phân trang. Ngừng sử dụng thay xóa cứng.
- Employee: các email riêng, mã tối đa 20, phòng ban/chức vụ/lịch bắt buộc; select dữ liệu thật, eager loading, unique ignore khi sửa. Nghỉ việc chuyển resigned. Thay phòng/chức vụ ghi audit, không dùng bảng điều chuyển cũ.
- ContractController/ContractService: HR/Director/Admin tạo/sửa draft hoặc rejected và trình duyệt; Director/Admin duyệt/từ chối có lý do. Hợp đồng approved khóa nội dung. Phê duyệt khóa Employee trong transaction và chặn khoảng hiệu lực chồng lấn. previous_contract_id phải thuộc cùng nhân viên và trỏ hợp đồng được duyệt trước đó.
- UserController/AccountInvitationService: HR/Director/Admin chủ động mời sau khi có hợp đồng approved; luồng mời nhân viên tạo Role Employee. Chỉ Admin được đổi vai trò qua chức năng quản lý tài khoản. Password NULL, pending_reset; SHA-256 của token lưu trên users, hết hạn 24 giờ. Request lặp không tạo thêm tài khoản/token/email.
- Mail tại app/Services/AccountInvitationService.php gọi Laravel Mail sau transaction tạo dữ liệu; nội dung tại app/Mail/AccountInvitationMail.php và resources/views/emails/account-invitation.blade.php. Không chứa mật khẩu, không dùng mailer log/array. Thành công chỉ báo máy chủ mail đã tiếp nhận, không khẳng định thư vào Inbox.
- Mail lỗi: giữ tài khoản pending_reset, vô hiệu hóa token, ghi audit lỗi với nội dung tổng quát. Không ghi exception SMTP hoặc token gốc. Chưa triển khai gửi lại lời mời cho tài khoản đã tồn tại, kể cả token hết hạn/lần gửi lỗi.
- AccountInvitationController: kiểm tra hash/trạng thái/hết hạn cả GET và POST; khóa User khi đặt password, hash mật khẩu, set activated_at/active và xóa token trong một transaction. Mật khẩu tối thiểu 8 ký tự, có hoa/thường/số, tối đa 72 byte.
- Khóa/mở khóa: HR/Director thao tác tài khoản Employee đã kích hoạt; Admin thao tác mọi vai trò khác tài khoản đang sử dụng. Kiểm tra active ở middleware trên mỗi request; clear remember_token khi đổi trạng thái/vai trò.
- Dashboard bỏ số liệu mẫu, dùng truy vấn thật. Vùng cá nhân chỉ lấy employee_id của user hiện tại, không nhận ID nhân viên từ client.
- Không có chức năng/package Excel hiện hữu trong các thành phần được kiểm tra; không cài package hoặc tạo module xuất mới.

## Chấm công, lương và OT đã chốt

- Thời điểm vào/ra lấy từ máy chủ. Một bản ghi/nhân viên/ngày. Thời gian được hiểu theo timezone ứng dụng; cần kiểm tra timezone môi trường trước khi sử dụng thực tế.
- Công được tính theo khoảng giao với hai phần của ca, trừ nghỉ trưa; đi muộn trong 5 phút được tính từ đầu ca. Sau thời gian cho phép, late_minutes tính từ giờ bắt đầu ca.
- Ví dụ 08:15–17:00: 465 phút, work_units 0.9688. Bảng lương cộng phút rồi quy đổi một lần; không cộng các ngày đã làm tròn để tính tiền.
- Công chuẩn tháng đếm ngày làm việc theo lịch, không cố định 26 ngày.
- Thiếu bản ghi ngày làm việc hoặc quên check-out: báo chờ HR xử lý và chặn tính lương; không tự suy ra 0 công. Trang quản lý chấm công có kiểm tra ngày thiếu khi lọc nhân viên/khoảng ngày.
- HR xác nhận công nghỉ lễ/nghỉ phép bằng regular_minutes và lý do bắt buộc. Không có lịch lễ/quản lý phép riêng trong schema này.
- OT ngoài ca chỉ là đề xuất pending. HR xác nhận phút, loại ngày (kể cả lễ/nghỉ bù), duyệt/từ chối có lý do. Không tự trả OT chưa duyệt.
- Gợi ý lương giờ = (base_salary × salary_coefficient + allowance) / giờ chuẩn tháng. Do allowance chưa tách loại, HR bắt buộc đối chiếu tiền lương thực trả và các khoản được đưa vào căn cứ, rồi xác nhận hoặc sửa có lý do. Đây không phải suy luận rằng mọi khoản phụ cấp đều được tính OT.
- Hệ số OT ban ngày được gợi ý và chặn dưới mức tối thiểu theo loại ngày: 1.5/2/3. Lương giờ/hệ số điều chỉnh phải có lý do. Căn cứ từng ngày, hệ số, phút, số tiền và người xác nhận lưu trong calculation_snapshot.
- Lương theo công + phụ cấp + OT − khấu trừ. Tính tiền bằng số nguyên thập phân, làm tròn HALF_UP đến cent; không dùng float để tính số tiền. Khấu trừ > 0 cần ghi căn cứ.
- Một tháng có nhiều hợp đồng hiệu lực cho một nhân viên bị chặn vì chỉ có một contract_id/dòng lương.
- HR tính → trình; Director duyệt → **tự động published**, lưu approved_at/published_at trong cùng transaction. Không có nút công bố riêng của HR. Nhân viên chỉ thấy published.
- Chấm công khóa khi kỳ lương pending_approval/approved/published. Trước trình và duyệt, kiểm tra snapshot còn khớp nguồn. Bảng lương đã công bố không bị tính lại.
- Audit tập trung ở app/Services/AuditService.php; các thao tác HR/Director, tạo/kích hoạt/khóa tài khoản, hợp đồng, công/lương đều gọi trong transaction tương ứng. Không có CRUD sửa/xóa audit, không ghi password/remember_token/token.

Nguồn đối chiếu OT: [Điều 98 và mức 150%/200%/300%](https://baochinhphu.vn/tinh-luong-them-gio-khi-ngay-le-trung-ngay-nghi-hang-tuan-102292425.htm); [căn cứ lương thực trả và loại khoản hỗ trợ cần loại trừ](https://www2.en.baochinhphu.vn/can-cu-tinh-tien-luong-lam-them-gio-cho-nguoi-lao-dong-10226080511012056.htm); [ngày nghỉ bù hưởng hệ số ngày nghỉ hằng tuần](https://baochinhphu.vn/cach-tinh-luong-lam-them-gio-vao-ngay-nghi-bu-102260817092354894.htm). Chức năng hiện tại chưa bao quát toàn bộ điều kiện pháp luật về tổ chức làm thêm hoặc công việc đặc thù.

## Seeder và bootstrap

RoleSeeder tạo HR, Director, Employee, Admin theo thứ tự trên database rỗng sau khi chạy migration mở rộng enum. Department/Position có code, WorkScheduleSeeder có ca mặc định, EmployeeSeeder tạo hồ sơ mẫu pending với FK hợp lệ. UserFactory/EmployeeFactory đã đồng bộ.

Theo yêu cầu mới, BootstrapUserSeeder dùng ba tài khoản mẫu cố định do người dùng chỉ định: director@hrm.com (Director), hr@hrm.com (HR), nv1@hrm.com (Employee). Mật khẩu đều dài 8 ký tự, được hash trước khi lưu database; seeder không in hoặc gửi mật khẩu qua email. Không còn yêu cầu biến môi trường hoặc nhập mật khẩu trong terminal.

Chạy `php artisan db:seed --class=BootstrapUserSeeder` để tạo/cập nhật Director, HR và Nhân viên 1; `php artisan db:seed` tạo/cập nhật thêm Admin thành bốn tài khoản. DirectorUserSeeder dùng chung logic, chỉ xử lý Director khi chạy riêng bằng `php artisan db:seed --class=DirectorUserSeeder`. Hồ sơ BOOT-DIRECTOR, BOOT-HR và Nhân viên 1 (mã NV tự sinh, giữ mã khi đã tồn tại) ở trạng thái working, có phòng ban, chức vụ và lịch làm việc; tài khoản active có activated_at, không cần invitation. Chạy lại giữ ID, khôi phục mật khẩu đã chỉ định và trạng thái active, xóa token kích hoạt/remember token. Hồ sơ BOOT-DIRECTOR cũ được cập nhật sang email director@hrm.com. Giao dịch rollback toàn bộ nhóm nếu có xung đột tài khoản/hồ sơ; không tự nâng quyền tài khoản khác. Không tạo hoặc tự duyệt hợp đồng/lương. Chưa thực thi seeder trên database của bạn.

Đã cập nhật DirectorUserSeederTest để kiểm tra ba tài khoản đăng nhập, chạy lại không trùng, cập nhật Director cũ và rollback khi trùng email. HrmWorkflowTest kiểm tra từ chối mật khẩu 7 ký tự và chấp nhận 8 ký tự khi kích hoạt. Chỉ kiểm tra tĩnh/PHP lint; chưa chạy các feature test vì giữ phạm vi không nạp vendor/.env.

## Kiểm tra thực tế

| Kiểm tra | Kết quả |
|---|---|
| PHP lint | 63 file PHP: đạt. Các file chỉnh sau lượt kiểm tra được lint lại. |
| Phép tính độc lập | 57 kiểm tra chạy qua tests/Unit/StandaloneCalculators.php: đạt. Không nạp Laravel/vendor/.env, không DB/SMTP. |
| Blade | Kiểm tra tĩnh cú pháp biểu thức và cấu trúc điều khiển của 49 Blade bằng PHP parser: đạt. Không thay thế việc compile/render bằng Blade thật. |
| Route/view | 66 tên route; đối chiếu tĩnh route được gọi, view/include/extends và phương thức Controller: không thấy tham chiếu thiếu. |
| Model/schema | Fillable 11 Model khớp các cột migration; kiểm tra thứ tự 20 FK và không trùng tạo bảng: đạt ở mức tĩnh. |
| Tham chiếu cũ | Không còn các tham chiếu nghiệp vụ cũ trong danh sách mã nguồn đang dùng đã rà soát. |
| Feature tests | Viết 16 test luồng HRM, có bảo vệ chỉ dùng DB MySQL/MariaDB riêng đuôi _test/_testing. **Chưa chạy**. |
| Laravel/DB/UI/mail | Chưa chạy route:list, migrate:status, migrations/seeder, feature test, trình duyệt hoặc SMTP thật do phạm vi không cho nạp vendor/.env. |

Test tích hợp đã chuẩn bị: redirect/login/locked/pending, chặn URL vượt quyền, Employee update/resigned, code unique/name trùng, hợp đồng chồng lấn/khóa nội dung, lời mời đủ điều kiện/idempotency/lỗi mail, activation một lần/hết hạn, chấm công theo giờ máy chủ, lương duyệt tự công bố/chỉ xem của mình, thiếu công, lịch đã có chấm công, chặn client nâng role, lý do điều chỉnh/từ chối.

## Triển khai và các giới hạn còn lại

1. **Migration hiện tại là baseline cho DB mới, không phải script chuyển đổi an toàn dữ liệu cũ.** Sửa migration đã chạy không tự đổi cấu trúc DB đang dùng. Không chạy migrate:fresh trên database có dữ liệu cần giữ. Không có thay đổi database thực tế nào trong lần làm này.
2. Với DB cũ phải sao lưu, dựng DB thử nghiệm riêng, chuyển dữ liệu có mapping đã được xác nhận rồi đối soát. Cần xử lý user cũ không có employee, username→email, role Admin→HR/Director, email trùng, lịch làm việc, hợp đồng/lương lịch sử. Không tự suy diễn hoặc làm mất lịch sử 4 bảng đã bỏ.
3. Với DB test rỗng/được phép xóa, người dùng tự cấu hình tiến trình test trỏ MySQL/MariaDB riêng có tên đuôi _test/_testing (UTF8MB4), sau đó mới chạy:
   - php artisan migrate:fresh --seed
   - php artisan route:list
   - php artisan migrate:status
   - php artisan test --filter=HrmWorkflowTest
   - php tests/Unit/StandaloneCalculators.php
   Không chạy các lệnh migrate/seed này trên production.
4. Hợp đồng đã duyệt không có ngày kết thúc sẽ chặn bản sửa đổi chồng lấn. Cần chốt nghiệp vụ chấm dứt hợp đồng; chưa tự sửa hợp đồng approved để lách kiểm tra.
5. Chưa hỗ trợ chia lương nhiều hợp đồng/tháng, ca qua đêm/OT ban đêm, nhiều ca/ngày, hoặc nhiều lịch có công chuẩn tháng khác nhau trong một kỳ. Các trường hợp này bị chặn rõ ràng.
6. Lịch đã gán nhân viên khóa các tham số tính công; nhân viên đã có chấm công không đổi work_schedule_id hồi tố. Chuyển lịch cần thiết kế lịch sử phân ca.
7. Ngày lễ, nghỉ bù và công nghỉ phép hưởng lương do HR xác nhận. Schema chưa có danh mục phép/ngày lễ hoặc ngày nghỉ việc cụ thể. Căn cứ lương giờ gợi ý cần HR kiểm chứng vì chưa phân loại phụ cấp.
8. Chưa có gửi lại token hết hạn/gửi lỗi hoặc phục hồi lần gửi bị gián đoạn; request lặp không phát sinh thư mới. SMTP chỉ xác nhận tiếp nhận, không bảo đảm thư vào Inbox; không có outbox/delivery receipt trong schema.
9. Chưa kiểm tra timezone, APP_URL, mail config, kết nối DB hoặc thông tin SMTP; không đọc/sửa .env, credentials, vendor, .git hoặc storage/logs.
10. Chưa có giới hạn IP chấm công vì chưa có cấu hình mạng được xác nhận. Không tạo dải IP giả.

## File đã đọc trước khi sửa

Ngoài danh sách dưới đây, đã đọc composer.json (chỉ xác nhận dependency), tests/TestCase.php (lớp cha của feature test), file SQL đính kèm và nội dung yêu cầu tại C:/Users/Admin/.codex/attachments/703b7e98-6701-43ae-8be0-4afecd4fcc00/Pasted text.txt. Chỉ một lượt lập danh sách thư mục nguồn; các lần kiểm tra sau dùng đường dẫn cụ thể.

- [app/Http/Controllers/AccountInvitationController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/AccountInvitationController.php)
- [app/Http/Controllers/AttendanceController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/AttendanceController.php)
- [app/Http/Controllers/AuditLogController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/AuditLogController.php)
- [app/Http/Controllers/AuthController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/AuthController.php)
- [app/Http/Controllers/Controller.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/Controller.php)
- [app/Http/Controllers/DepartmentController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/DepartmentController.php)
- `app/Http/Controllers/EmployeeAssignmentController.php` (đã đọc trước khi loại bỏ)
- [app/Http/Controllers/EmployeeController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/EmployeeController.php)
- `app/Http/Controllers/EmployeeSalaryController.php` (đã đọc trước khi loại bỏ)
- [app/Http/Controllers/PayrollDetailController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/PayrollDetailController.php)
- [app/Http/Controllers/PayrollPeriodController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/PayrollPeriodController.php)
- [app/Http/Controllers/PositionController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/PositionController.php)
- `app/Http/Controllers/PositionSalaryController.php` (đã đọc trước khi loại bỏ)
- [app/Http/Controllers/UserController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/UserController.php)
- [app/Http/Middleware/RoleMiddleware.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Middleware/RoleMiddleware.php)
- [app/Mail/AccountInvitationMail.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Mail/AccountInvitationMail.php)
- `app/Models/AccountInvitation.php` (đã đọc trước khi loại bỏ)
- [app/Models/Attendance.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/Attendance.php)
- [app/Models/AuditLog.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/AuditLog.php)
- [app/Models/Department.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/Department.php)
- [app/Models/Employee.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/Employee.php)
- `app/Models/EmployeeAssignment.php` (đã đọc trước khi loại bỏ)
- `app/Models/EmployeeSalary.php` (đã đọc trước khi loại bỏ)
- [app/Models/PayrollDetail.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/PayrollDetail.php)
- [app/Models/PayrollPeriod.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/PayrollPeriod.php)
- [app/Models/Position.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/Position.php)
- `app/Models/PositionSalary.php` (đã đọc trước khi loại bỏ)
- [app/Models/Role.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/Role.php)
- [app/Models/User.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/User.php)
- [bootstrap/app.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/bootstrap/app.php)
- [database/factories/UserFactory.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/factories/UserFactory.php)
- [database/migrations/0001_01_01_000001_create_cache_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/0001_01_01_000001_create_cache_table.php)
- [database/migrations/0001_01_01_000002_create_jobs_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/0001_01_01_000002_create_jobs_table.php)
- [database/migrations/2026_09_19_063341_create_roles_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_19_063341_create_roles_table.php)
- [database/migrations/2026_09_19_063946_create_departments_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_19_063946_create_departments_table.php)
- [database/migrations/2026_09_19_064341_create_positions_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_19_064341_create_positions_table.php)
- [database/migrations/2026_09_19_064419_create_employees_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_19_064419_create_employees_table.php)
- [database/migrations/2026_09_19_064849_create_users_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_19_064849_create_users_table.php)
- `database/migrations/2026_09_21_152852_create_position_salaries_table.php` (đã đọc trước khi loại bỏ)
- `database/migrations/2026_09_21_152860_create_employee_salaries_table.php` (đã đọc trước khi loại bỏ)
- [database/migrations/2026_09_21_152918_create_attendances_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_21_152918_create_attendances_table.php)
- [database/migrations/2026_09_21_152932_create_payroll_periods_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_21_152932_create_payroll_periods_table.php)
- [database/migrations/2026_09_21_153002_create_payroll_details_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_21_153002_create_payroll_details_table.php)
- `database/migrations/2026_09_21_153017_create_employee_assignments_table.php` (đã đọc trước khi loại bỏ)
- `database/migrations/2026_09_21_153058_create_account_invitations_table.php` (đã đọc trước khi loại bỏ)
- [database/migrations/2026_09_21_153110_create_audit_logs_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_21_153110_create_audit_logs_table.php)
- `database/seeders/AdminUserSeeder.php` (đã đọc trước khi loại bỏ)
- [database/seeders/DatabaseSeeder.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/seeders/DatabaseSeeder.php)
- [database/seeders/DepartmentSeeder.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/seeders/DepartmentSeeder.php)
- [database/seeders/PositionSeeder.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/seeders/PositionSeeder.php)
- [database/seeders/RoleSeeder.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/seeders/RoleSeeder.php)
- [resources/views/admin/department/create.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/department/create.blade.php)
- [resources/views/admin/department/edit.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/department/edit.blade.php)
- [resources/views/admin/department/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/department/index.blade.php)
- [resources/views/admin/department/show.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/department/show.blade.php)
- [resources/views/admin/employee/create.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/employee/create.blade.php)
- [resources/views/admin/employee/edit.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/employee/edit.blade.php)
- [resources/views/admin/employee/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/employee/index.blade.php)
- [resources/views/admin/employee/show.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/employee/show.blade.php)
- [resources/views/admin/layout/masterlayout.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/layout/masterlayout.blade.php)
- [resources/views/admin/partial/footer.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/partial/footer.blade.php)
- [resources/views/admin/partial/header.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/partial/header.blade.php)
- [resources/views/admin/partial/sidebar.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/partial/sidebar.blade.php)
- [resources/views/admin/position/create.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/position/create.blade.php)
- [resources/views/admin/position/edit.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/position/edit.blade.php)
- [resources/views/admin/position/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/position/index.blade.php)
- [resources/views/admin/position/show.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/position/show.blade.php)
- [resources/views/admin/welcome.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/welcome.blade.php)
- [resources/views/auth/activate-account.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/auth/activate-account.blade.php)
- [resources/views/auth/login.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/auth/login.blade.php)
- [resources/views/emails/account-invitation.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/emails/account-invitation.blade.php)
- [resources/views/user/layout/masterlayout.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/user/layout/masterlayout.blade.php)
- [resources/views/user/partial/footer.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/user/partial/footer.blade.php)
- [resources/views/user/partial/header.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/user/partial/header.blade.php)
- [resources/views/user/partial/sidebar.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/user/partial/sidebar.blade.php)
- [resources/views/user/profile/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/user/profile/index.blade.php)
- [routes/web.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/routes/web.php)
- [tests/Feature/ExampleTest.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/tests/Feature/ExampleTest.php)
- [tests/Unit/ExampleTest.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/tests/Unit/ExampleTest.php)

## File đã chỉnh sửa (59)

- [app/Http/Controllers/AccountInvitationController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/AccountInvitationController.php)
- [app/Http/Controllers/AttendanceController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/AttendanceController.php)
- [app/Http/Controllers/AuditLogController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/AuditLogController.php)
- [app/Http/Controllers/AuthController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/AuthController.php)
- [app/Http/Controllers/DepartmentController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/DepartmentController.php)
- [app/Http/Controllers/EmployeeController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/EmployeeController.php)
- [app/Http/Controllers/PayrollDetailController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/PayrollDetailController.php)
- [app/Http/Controllers/PayrollPeriodController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/PayrollPeriodController.php)
- [app/Http/Controllers/PositionController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/PositionController.php)
- [app/Http/Controllers/UserController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/UserController.php)
- [app/Http/Middleware/RoleMiddleware.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Middleware/RoleMiddleware.php)
- [app/Mail/AccountInvitationMail.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Mail/AccountInvitationMail.php)
- [app/Models/Attendance.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/Attendance.php)
- [app/Models/AuditLog.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/AuditLog.php)
- [app/Models/Department.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/Department.php)
- [app/Models/Employee.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/Employee.php)
- [app/Models/PayrollDetail.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/PayrollDetail.php)
- [app/Models/PayrollPeriod.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/PayrollPeriod.php)
- [app/Models/Position.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/Position.php)
- [app/Models/Role.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/Role.php)
- [app/Models/User.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/User.php)
- [bootstrap/app.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/bootstrap/app.php)
- [database/factories/UserFactory.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/factories/UserFactory.php)
- [database/migrations/2026_09_19_063341_create_roles_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_19_063341_create_roles_table.php)
- [database/migrations/2026_09_19_063946_create_departments_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_19_063946_create_departments_table.php)
- [database/migrations/2026_09_19_064341_create_positions_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_19_064341_create_positions_table.php)
- [database/migrations/2026_09_19_064419_create_employees_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_19_064419_create_employees_table.php)
- [database/migrations/2026_09_19_064849_create_users_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_19_064849_create_users_table.php)
- [database/migrations/2026_09_21_152918_create_attendances_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_21_152918_create_attendances_table.php)
- [database/migrations/2026_09_21_152932_create_payroll_periods_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_21_152932_create_payroll_periods_table.php)
- [database/migrations/2026_09_21_153002_create_payroll_details_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_21_153002_create_payroll_details_table.php)
- [database/migrations/2026_09_21_153110_create_audit_logs_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_21_153110_create_audit_logs_table.php)
- [database/seeders/DatabaseSeeder.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/seeders/DatabaseSeeder.php)
- [database/seeders/DepartmentSeeder.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/seeders/DepartmentSeeder.php)
- [database/seeders/PositionSeeder.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/seeders/PositionSeeder.php)
- [database/seeders/RoleSeeder.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/seeders/RoleSeeder.php)
- [resources/views/admin/department/create.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/department/create.blade.php)
- [resources/views/admin/department/edit.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/department/edit.blade.php)
- [resources/views/admin/department/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/department/index.blade.php)
- [resources/views/admin/department/show.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/department/show.blade.php)
- [resources/views/admin/employee/create.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/employee/create.blade.php)
- [resources/views/admin/employee/edit.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/employee/edit.blade.php)
- [resources/views/admin/employee/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/employee/index.blade.php)
- [resources/views/admin/employee/show.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/employee/show.blade.php)
- [resources/views/admin/layout/masterlayout.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/layout/masterlayout.blade.php)
- [resources/views/admin/partial/header.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/partial/header.blade.php)
- [resources/views/admin/partial/sidebar.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/partial/sidebar.blade.php)
- [resources/views/admin/position/create.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/position/create.blade.php)
- [resources/views/admin/position/edit.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/position/edit.blade.php)
- [resources/views/admin/position/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/position/index.blade.php)
- [resources/views/admin/position/show.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/position/show.blade.php)
- [resources/views/admin/welcome.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/welcome.blade.php)
- [resources/views/auth/activate-account.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/auth/activate-account.blade.php)
- [resources/views/auth/login.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/auth/login.blade.php)
- [resources/views/emails/account-invitation.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/emails/account-invitation.blade.php)
- [resources/views/user/layout/masterlayout.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/user/layout/masterlayout.blade.php)
- [resources/views/user/profile/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/user/profile/index.blade.php)
- [routes/web.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/routes/web.php)
- [tests/Feature/ExampleTest.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/tests/Feature/ExampleTest.php)

## File mới (46, gồm báo cáo này)

- [app/Http/Controllers/ContractController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/ContractController.php)
- [app/Http/Controllers/DashboardController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/DashboardController.php)
- [app/Http/Controllers/WorkScheduleController.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Http/Controllers/WorkScheduleController.php)
- [app/Models/Contract.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/Contract.php)
- [app/Models/WorkSchedule.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Models/WorkSchedule.php)
- [app/Services/AccountInvitationService.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Services/AccountInvitationService.php)
- [app/Services/AttendanceService.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Services/AttendanceService.php)
- [app/Services/AuditService.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Services/AuditService.php)
- [app/Services/ContractService.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Services/ContractService.php)
- [app/Services/PayrollCalculator.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Services/PayrollCalculator.php)
- [app/Services/PayrollService.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Services/PayrollService.php)
- [app/Services/WorkTimeCalculator.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/app/Services/WorkTimeCalculator.php)
- [database/factories/EmployeeFactory.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/factories/EmployeeFactory.php)
- [database/migrations/2026_09_19_064350_create_work_schedules_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_19_064350_create_work_schedules_table.php)
- [database/migrations/2026_09_21_152900_create_contracts_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_21_152900_create_contracts_table.php)
- [database/migrations/2026_09_21_153200_create_sessions_table.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/2026_09_21_153200_create_sessions_table.php)
- [database/migrations/SCHEMA_SYNC_REPORT.md](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/migrations/SCHEMA_SYNC_REPORT.md)
- [database/seeders/BootstrapUserSeeder.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/seeders/BootstrapUserSeeder.php)
- [database/seeders/EmployeeSeeder.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/seeders/EmployeeSeeder.php)
- [database/seeders/WorkScheduleSeeder.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/database/seeders/WorkScheduleSeeder.php)
- [resources/views/admin/attendance/_form.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/attendance/_form.blade.php)
- [resources/views/admin/attendance/create.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/attendance/create.blade.php)
- [resources/views/admin/attendance/edit.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/attendance/edit.blade.php)
- [resources/views/admin/attendance/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/attendance/index.blade.php)
- [resources/views/admin/audit/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/audit/index.blade.php)
- [resources/views/admin/contract/_form.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/contract/_form.blade.php)
- [resources/views/admin/contract/create.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/contract/create.blade.php)
- [resources/views/admin/contract/edit.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/contract/edit.blade.php)
- [resources/views/admin/contract/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/contract/index.blade.php)
- [resources/views/admin/contract/show.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/contract/show.blade.php)
- [resources/views/admin/department/_form.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/department/_form.blade.php)
- [resources/views/admin/employee/_form.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/employee/_form.blade.php)
- [resources/views/admin/partial/alerts.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/partial/alerts.blade.php)
- [resources/views/admin/partial/status.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/partial/status.blade.php)
- [resources/views/admin/payroll/create.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/payroll/create.blade.php)
- [resources/views/admin/payroll/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/payroll/index.blade.php)
- [resources/views/admin/payroll/show.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/payroll/show.blade.php)
- [resources/views/admin/position/_form.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/position/_form.blade.php)
- [resources/views/admin/schedule/form.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/schedule/form.blade.php)
- [resources/views/admin/schedule/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/schedule/index.blade.php)
- [resources/views/admin/user/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/admin/user/index.blade.php)
- [resources/views/user/attendance/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/user/attendance/index.blade.php)
- [resources/views/user/contract/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/user/contract/index.blade.php)
- [resources/views/user/payroll/index.blade.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/resources/views/user/payroll/index.blade.php)
- [tests/Feature/HrmWorkflowTest.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/tests/Feature/HrmWorkflowTest.php)
- [tests/Unit/StandaloneCalculators.php](C:/xampp/htdocs/MNMNC/Webquanlynhansu/tests/Unit/StandaloneCalculators.php)

## File đã ngừng sử dụng và loại bỏ (12)

Đã kiểm tra các phụ thuộc trước khi loại bỏ. Controller kích hoạt vẫn giữ tên AccountInvitationController nhưng dùng trực tiếp users, không còn Model/bảng account_invitations.

- `app/Http/Controllers/EmployeeAssignmentController.php`
- `app/Http/Controllers/EmployeeSalaryController.php`
- `app/Http/Controllers/PositionSalaryController.php`
- `app/Models/AccountInvitation.php`
- `app/Models/EmployeeAssignment.php`
- `app/Models/EmployeeSalary.php`
- `app/Models/PositionSalary.php`
- `database/migrations/2026_09_21_152852_create_position_salaries_table.php`
- `database/migrations/2026_09_21_152860_create_employee_salaries_table.php`
- `database/migrations/2026_09_21_153017_create_employee_assignments_table.php`
- `database/migrations/2026_09_21_153058_create_account_invitations_table.php`
- `database/seeders/AdminUserSeeder.php`

