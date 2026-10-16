# Form Request trong HRM

Website sử dụng Blade. Các class ở đây nhận và kiểm tra dữ liệu đầu vào; Controller nhận dữ liệu đã hợp lệ rồi gọi nghiệp vụ và trả View/redirect.

| Nhóm chức năng | Form Request |
|---|---|
| Đăng nhập, kích hoạt | LoginRequest, ActivateAccountRequest |
| Nhân viên | FilterEmployeesRequest, SaveEmployeeRequest |
| Hợp đồng | FilterContractsRequest, SaveContractRequest, RejectApprovalRequest |
| Tài khoản | SendAccountInvitationRequest, UpdateUserStatusRequest, UpdateUserRoleRequest |
| Phòng ban/chức vụ | FilterCatalogRequest, SaveDepartmentRequest, SavePositionRequest, UpdateCatalogStatusRequest |
| Chấm công | FilterAttendancesRequest, SaveAttendanceRequest |
| Lịch làm việc | SaveWorkScheduleRequest |
| Bảng lương | StorePayrollPeriodRequest, CalculatePayrollRequest, RejectApprovalRequest |
| Nhật ký | FilterAuditLogsRequest |

- `authorize()` kiểm tra quyền: HrmRequest dùng chung cho HR/Director/Admin đang active; đổi vai trò chỉ dành cho Admin; từ chối phê duyệt chỉ dành cho Director/Admin. Middleware và kiểm tra nghiệp vụ phía Service vẫn áp dụng.
- `rules()` khai báo kiểu dữ liệu, độ dài, unique, FK và các trường không cho client gửi lên. Khi cập nhật, ID được lấy từ model do route binding cung cấp, không lấy ID tùy ý trong form để ignore unique.
- Controller gọi `$request->validated()` để lấy dữ liệu đã kiểm tra. Các request cần loại mã tự sinh hoặc chuẩn hóa dữ liệu cung cấp thêm `payload()`.
- SaveEmployeeRequest và SaveContractRequest loại mã khỏi payload; backend sinh mã NV/HD. SaveAttendanceRequest giữ nguyên nhân viên/ngày của bản ghi đang sửa. SaveWorkScheduleRequest tính phút chuẩn, sắp xếp ngày làm việc và chuẩn hóa giờ.
- ActivateAccountRequest từ chối token hết hạn/đã dùng bằng HTTP 410 trước khi kiểm tra mật khẩu. Transaction kích hoạt vẫn khóa và kiểm tra token lần nữa để tránh dùng hai lần.
- Quy tắc phụ thuộc dữ liệu đang bị thay đổi đồng thời (hợp đồng đã khóa, chồng thời gian, đổi lịch sau chấm công, trạng thái tài khoản) vẫn được kiểm tra trong transaction của Controller/Service.

Ví dụ Controller:

```php
public function store(SaveContractRequest $request, ContractService $service)
{
    $contract = $service->save($request->user(), $request->payload());
    return redirect()->route('contracts.show', $contract);
}
```

Khi thêm hoặc sửa field, cập nhật Request tương ứng và View. Chỉ thêm Service khi cần tổ chức nghiệp vụ, không chuyển truy vấn hoặc ghi database vào `rules()`.
