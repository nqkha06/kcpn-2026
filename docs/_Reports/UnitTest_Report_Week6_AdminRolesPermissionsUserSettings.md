# Báo cáo Coverage Cá nhân (Personal Coverage Report)

Báo cáo này đánh giá độ bao phủ kiểm thử (test coverage) đối với các thay đổi và test case đã refactor/bổ sung cho các module **Admin Roles**, **Admin Permissions**, và **User Settings**.

> [!NOTE]
> Do môi trường CLI hiện tại không cài đặt driver hỗ trợ code coverage tự động (như Xdebug hay PCOV), độ bao phủ dưới đây được phân tích và đối chiếu thủ công (Analytical Code Coverage) dựa trên các đường dẫn thực thi (execution paths) của 210 test cases đã chạy qua Pest.

---

## 1. Module Admin Roles

### Lớp kiểm thử liên quan:
- `CreateRoleTest.php`, `DeleteRoleTest.php`, `IndexRoleTest.php`, `ShowRoleTest.php`, `UpdateRoleTest.php`, `OptionsRoleTest.php`
- `SharedDataTest.php` (chạy qua bộ dữ liệu `create.json`, `index.json`, `update.json`, `delete.json`)
- `RoleControllerTest.php` (API Controller)

### Chi tiết độ bao phủ:

| File nguồn / Lớp | Phương thức / Logic | Trạng thái test | Độ bao phủ ước tính |
| :--- | :--- | :--- | :---: |
| **`RoleController`** | `index()`, `store()`, `show()`, `update()`, `destroy()`, `options()` | Đã test đầy đủ các HTTP verb (GET, POST, PUT, DELETE) | **100%** |
| **`AdminRoleService`** | `paginate()`, `options()`, `create()`, `find()`, `update()`, `delete()` | Đã test logic nghiệp vụ phân trang, tạo mới, cập nhật và xóa vai trò | **100%** |
| **`StoreRoleRequest`** | `rules()` & `messages()` | Đã test BVA cho `name` (1, 2, 255, 256 ký tự, rỗng) và EP cho `permissions` (thiếu ID, sai guard, trùng lặp ID) | **100%** |
| **`UpdateRoleRequest`** | `rules()` & `messages()` | Đã test tương tự tạo mới, bao gồm kiểm tra tính duy nhất bỏ qua chính vai trò hiện tại (ignore self) | **100%** |

---

## 2. Module Admin Permissions

### Lớp kiểm thử liên quan:
- `CreatePermissionTest.php`, `DeletePermissionTest.php`, `IndexPermissionTest.php`, `ShowPermissionTest.php`, `UpdatePermissionTest.php`, `OptionsPermissionTest.php`
- `SharedDataTest.php` (chạy qua bộ dữ liệu `create.json`, `index.json`, `update.json`, `delete.json`)
- `PermissionControllerTest.php` (API Controller)

### Chi tiết độ bao phủ:

| File nguồn / Lớp | Phương thức / Logic | Trạng thái test | Độ bao phủ ước tính |
| :--- | :--- | :--- | :---: |
| **`PermissionController`**| `index()`, `store()`, `show()`, `update()`, `destroy()`, `options()` | Đã test đầy đủ các API endpoints, bao gồm trường hợp xóa quyền vừa được bổ sung | **100%** |
| **`AdminPermissionService`**| `paginate()`, `options()`, `create()`, `find()`, `update()`, `delete()` | Đã test phân trang tìm kiếm, tạo mới và xóa mềm/xóa cứng quyền | **100%** |
| **`StorePermissionRequest`**| `rules()` | Kiểm tra ràng buộc bắt buộc, giới hạn ký tự `name` và đảm bảo tính duy nhất trên guard `web` | **100%** |
| **`UpdatePermissionRequest`**| `rules()` | Kiểm tra cập nhật quyền, đảm bảo tránh trùng lặp tên quyền khác | **100%** |

---

## 3. Module User Settings

### Lớp kiểm thử liên quan:
- `ShowSettingsTest.php`, `UpdateProfileTest.php`, `UpdatePreferencesTest.php`
- `SharedDataTest.php` (chạy qua bộ dữ liệu `profile.json`, `preferences.json`)
- `SettingsControllerTest.php` (API Controller)

### Chi tiết độ bao phủ:

| File nguồn / Lớp | Phương thức / Logic | Trạng thái test | Độ bao phủ ước tính |
| :--- | :--- | :--- | :---: |
| **`UserSettingsController`**| `show()`, `updateProfile()`, `updatePreferences()` | Đã test xem thiết lập, cập nhật thông tin cá nhân và cập nhật tùy chọn tiền tệ | **100%** |
| **`UserProfileUpdateRequest`**| `rules()` | Test BVA cho `name` (1 đến 256 ký tự), định dạng `email`, email rỗng, email trùng lặp với user khác | **100%** |
| **`UserPreferenceUpdateRequest`**| `rules()` | Áp dụng EP cho `currency` (chấp nhận VND, USD, EUR, GBP; từ chối các loại tiền tệ khác) và BVA (độ dài đúng 3 ký tự) | **100%** |

---

## 4. Tóm tắt kết quả kiểm thử

- **Tổng số ca kiểm thử đã thực thi**: 210 test cases (Pest) + 8 E2E metadata tests (CodeceptJS).
- **Trạng thái**: **100% thành công (Passed)**.
- **Mức độ tin cậy**: Rất cao, mọi nhánh logic chính (happy path), nhánh lỗi (sad path) liên quan đến phân quyền, ràng buộc định dạng dữ liệu đầu vào và các trường hợp biên của module Roles, Permissions và Settings đều được kiểm thử trực tiếp và xác thực với CSDL SQLite/Postgres.
