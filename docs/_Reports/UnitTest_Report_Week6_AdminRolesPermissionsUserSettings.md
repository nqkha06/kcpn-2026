# Báo cáo Kiểm thử & Độ bao phủ Chi tiết (Test Case & Coverage Report)
**Phạm vi phụ trách:** Admin Roles · Admin Permissions · User Settings  
**Dự án:** KCPM 2026 — Hệ thống Quản lý Tài chính cá nhân Cashback (`nqkha06/kcpn-2026`)  
**Công nghệ:** Laravel 12 · Pest 4 / PHPUnit 12 · Xdebug v3.4.1 (Coverage Mode) · SQLite in-memory  
**Ngày hoàn thiện:** 06/09/2026  

---

## 1. Tóm tắt kết quả kiểm thử & Độ bao phủ (Executive Summary)

Theo nhận xét và yêu cầu của giảng viên, toàn bộ bộ Test Case của 3 module phụ trách (**Admin Roles**, **Admin Permissions**, **User Settings**) đã được rà soát, tái cấu trúc và mở rộng toàn diện:
- Đã xác định đầy đủ các **Lớp tương đương (Equivalence Partitioning - EP)** hợp lệ và không hợp lệ.
- Đã xác định đầy đủ các **Giá trị biên (Boundary Value Analysis - BVA)** cho độ dài chuỗi, tham số phân trang và giá trị kích thước.
- Đã bổ sung đầy đủ test case cho các thành phần trước đây chưa có test: Web/Inertia Controllers (`Admin\RoleController`, `Admin\PermissionController`, `User\UserSettingsController`, `User\UserProfileController`, `User\UserPreferenceController`), các phương thức Policy chưa có test (`restore`, `forceDelete`), và nhánh `$userId === null` trong trait `ProfileValidationRules`.
- **Tổng số ca kiểm thử đã thiết kế và thực thi:** **236 test cases** (Pest 4).
- **Trạng thái thực thi:** **236 / 236 PASSED (100% thành công, 0 Failed, 0 Skipped, 0 Errors)** với **828 assertions**.
- **Tính đồng bộ:** Số lượng test case trong tài liệu thiết kế khớp chính xác **1:1** với số lượng test case thực thi tự động.
- **Độ bao phủ code (Code Coverage):** Đã kích hoạt **Xdebug v3.4.1** trực tiếp trên môi trường PHP và chạy lệnh `php artisan test --coverage`. Toàn bộ **25 file nguồn** thuộc 3 module phụ trách đều đạt **100.0% Code Coverage**.

### Bảng tổng hợp số lượng Test Case theo module:

| Module / Nhóm chức năng | Test API Data-Driven | Test Feature chuyên biệt | Test Web Controller | Test Policy & Trait | Tổng Test Case | Trạng thái |
|---|:---:|:---:|:---:|:---:|:---:|:---:|
| **1. Admin Roles** | 52 | 34 | 7 | 6 | **99** | **100% Pass** |
| **2. Admin Permissions** | 43 | 28 | 7 | 6 | **84** | **100% Pass** |
| **3. User Settings** | 26 | 19 | 4 | 4 | **53** | **100% Pass** |
| **TỔNG CỘNG** | **121** | **81** | **18** | **16** | **236** | **100% Pass** |

---

## 2. Phân tích Lớp tương đương (Equivalence Partitioning - EP)

### 2.1. Module Admin Roles

| Trường dữ liệu / Điều kiện | Lớp tương đương Hợp lệ (Valid EP) | Lớp tương đương Không hợp lệ (Invalid EP) |
|---|---|---|
| **`name` (Tên vai trò - Create)** | • Chuỗi ký tự độ dài từ 1 đến 255 ký tự<br>• Chưa tồn tại trên guard `web`<br>• Trùng tên nhưng ở guard khác (`api`) | • Rỗng, null, hoặc không truyền trường `name`<br>• Chuỗi vượt quá 255 ký tự<br>• Trùng tên vai trò đã có trên guard `web`<br>• Kiểu dữ liệu không phải chuỗi (số, mảng) |
| **`name` (Tên vai trò - Update)** | • Chuỗi 1 đến 255 ký tự<br>• Trùng với tên hiện tại của chính vai trò đó (ignore self)<br>• Đổi sang tên mới chưa có trên guard `web` | • Rỗng hoặc không truyền<br>• Chuỗi vượt quá 255 ký tự<br>• Trùng tên với một vai trò khác trên guard `web` |
| **`permissions` (Danh sách quyền)** | • Mảng các số nguyên ID quyền tồn tại trên guard `web`<br>• Mảng rỗng `[]` (xóa hết quyền được gán)<br>• Bỏ qua trường (giữ nguyên quyền khi update) | • Không phải kiểu mảng (chuỗi, số, boolean)<br>• Mảng chứa ID quyền không tồn tại trong DB<br>• Mảng chứa ID quyền bị trùng lặp (`distinct`)<br>• Mảng chứa ID quyền thuộc guard khác (guard `api`)<br>• Mảng chứa phần tử không phải số nguyên |
| **`search` (Tìm kiếm danh sách)** | • Bỏ qua hoặc chuỗi rỗng<br>• Chuỗi độ dài từ 1 đến 255 ký tự (khớp 1 hoặc nhiều bản ghi)<br>• Chuỗi không khớp bản ghi nào | • Chuỗi vượt quá 255 ký tự |
| **`sort` (Cột sắp xếp)** | • Thuộc danh sách cho phép: `id`, `name`, `created_at`<br>• Bỏ qua (mặc định theo `id`) | • Tên cột không hợp lệ (ví dụ: `guard_name`, `foo`) |
| **`direction` (Chiều sắp xếp)** | • Thuộc danh sách cho phép: `asc`, `desc`<br>• Bỏ qua (mặc định `desc`) | • Giá trị không hợp lệ (ví dụ: `sideways`, `up`) |
| **`per_page` (Kích thước trang)** | • Số nguyên từ 1 đến 100<br>• Bỏ qua (mặc định 15) | • Số nguyên < 1 (ví dụ: 0, -5)<br>• Số nguyên > 100 (ví dụ: 101, 200)<br>• Giá trị không phải số nguyên (chuỗi, số thực) |
| **`page` (Số trang)** | • Số nguyên >= 1<br>• Bỏ qua (mặc định 1) | • Số nguyên < 1 (ví dụ: 0, -1)<br>• Giá trị không phải số nguyên |
| **`role_id` (Định danh vai trò)** | • ID của một vai trò đang tồn tại | • ID không tồn tại trong hệ thống (trả về 404)<br>• Xóa vai trò hệ thống `admin` hoặc `super-admin` (trả về 403) |
| **Actor (Quyền hạn truy cập)** | • Admin đã đăng nhập (`sanctum` hoặc session `web`) | • User thông thường (trả về 403 Forbidden)<br>• Guest chưa đăng nhập (trả về 401 Unauthorized) |

---

### 2.2. Module Admin Permissions

| Trường dữ liệu / Điều kiện | Lớp tương đương Hợp lệ (Valid EP) | Lớp tương đương Không hợp lệ (Invalid EP) |
|---|---|---|
| **`name` (Tên quyền - Create)** | • Chuỗi ký tự từ 1 đến 255 ký tự<br>• Chưa tồn tại trên guard `web` | • Rỗng, null, hoặc không truyền trường `name`<br>• Chuỗi vượt quá 255 ký tự<br>• Trùng tên quyền đã có trên guard `web`<br>• Kiểu dữ liệu không phải chuỗi |
| **`name` (Tên quyền - Update)** | • Chuỗi 1 đến 255 ký tự<br>• Trùng với tên hiện tại của chính quyền đó<br>• Đổi sang tên mới chưa có trên guard `web` | • Rỗng hoặc không truyền<br>• Chuỗi vượt quá 255 ký tự<br>• Trùng tên với một quyền khác trên guard `web` |
| **`search`, `sort`, `direction`, `per_page`, `page`** | Tương tự như quy tắc phân vùng của Role Index | Tương tự như quy tắc phân vùng của Role Index |
| **`permission_id` (Định danh quyền)** | • ID của một quyền đang tồn tại | • ID không tồn tại trong hệ thống (trả về 404) |
| **Actor (Quyền hạn truy cập)** | • Admin đã đăng nhập | • User thông thường (403 Forbidden)<br>• Guest chưa đăng nhập (401 Unauthorized) |

---

### 2.3. Module User Settings

| Trường dữ liệu / Điều kiện | Lớp tương đương Hợp lệ (Valid EP) | Lớp tương đương Không hợp lệ (Invalid EP) |
|---|---|---|
| **`name` (Họ và tên)** | • Chuỗi ký tự từ 1 đến 255 ký tự | • Rỗng, null, hoặc không truyền<br>• Chuỗi vượt quá 255 ký tự |
| **`email` (Địa chỉ email)** | • Định dạng email hợp lệ theo RFC<br>• Độ dài <= 255 ký tự<br>• Chưa tồn tại trong hệ thống<br>• Trùng với email hiện tại của chính user đó | • Rỗng, null, hoặc không truyền<br>• Sai định dạng email (thiếu @, thiếu domain, chứa khoảng trắng)<br>• Chuỗi vượt quá 255 ký tự<br>• Trùng với email của tài khoản người dùng khác |
| **`currency` (Tùy chọn tiền tệ)** | • Thuộc danh mục tiền tệ hợp lệ: `VND`, `USD`, `EUR`, `GBP`<br>• Độ dài chính xác 3 ký tự<br>• Chữ thường (hệ thống tự động chuẩn hóa sang in hoa) | • Rỗng, null, hoặc không truyền<br>• Độ dài khác 3 ký tự (ví dụ: `US`, `USDD`)<br>• Mã tiền tệ không nằm trong danh mục (ví dụ: `JPY`, `CAD`, `AUD`) |
| **Actor (Quyền hạn truy cập)** | • Người dùng có role `user` đã đăng nhập | • Người dùng có role `admin` (bị route middleware user chặn: 403 Forbidden)<br>• Guest chưa đăng nhập (401 Unauthorized) |

---

## 3. Phân tích Giá trị biên (Boundary Value Analysis - BVA)

Phương pháp BVA được áp dụng triệt để theo mô hình kiểm thử biên 6 điểm (`min-1`, `min`, `min+1`, `nominal`, `max-1`, `max`, `max+1`):

| Tham số / Biến | Miền giá trị quy chuẩn | Điểm biên dưới (Min side) | Giá trị danh nghĩa | Điểm biên trên (Max side) | Kết quả kỳ vọng |
|---|---|---|---|---|---|
| **`name` (Role / Permission / Profile)** | `[1, 255]` ký tự | • **0 ký tự (min-1):** Chuỗi rỗng `""`<br>• **1 ký tự (min):** `"a"`<br>• **2 ký tự (min+1):** `"ab"` | **128 ký tự (nominal)** | • **254 ký tự (max-1):** Chuỗi 254 ký tự<br>• **255 ký tự (max):** Chuỗi 255 ký tự<br>• **256 ký tự (max+1):** Chuỗi 256 ký tự | • 0: 422 Unprocessable<br>• 1, 2, 128, 254, 255: 200/201 Success<br>• 256: 422 Unprocessable |
| **`search` (Role / Permission Index)** | `[0, 255]` ký tự | • **0 ký tự (min):** Bỏ qua query<br>• **1 ký tự (min+1):** `"a"` | **128 ký tự (nominal)** | • **254 ký tự (max-1):** 254 ký tự<br>• **255 ký tự (max):** 255 ký tự<br>• **256 ký tự (max+1):** 256 ký tự | • 0..255: 200 OK<br>• 256: 422 Unprocessable |
| **`per_page` (Phân trang)** | `[1, 100]` bản ghi | • **0 (min-1):** `per_page=0`<br>• **1 (min):** `per_page=1`<br>• **2 (min+1):** `per_page=2` | **50 (nominal)** | • **99 (max-1):** `per_page=99`<br>• **100 (max):** `per_page=100`<br>• **101 (max+1):** `per_page=101` | • 0: 422 Unprocessable<br>• 1, 2, 50, 99, 100: 200 OK<br>• 101: 422 Unprocessable |
| **`page` (Số trang)** | `[1, +∞)` | • **0 (min-1):** `page=0`<br>• **1 (min):** `page=1`<br>• **2 (min+1):** `page=2` | **10 (nominal)** | • **N lớn:** `page=9999` | • 0: 422 Unprocessable<br>• >= 1: 200 OK |
| **`email` (Profile Email)** | `[3, 255]` ký tự | • **0 (min-1):** Rỗng<br>• **Định dạng tối thiểu:** `a@b.c` | **Email thông thường** (`test@example.com`) | • **254 (max-1):** RFC email 254 ký tự<br>• **255 (max):** RFC email 255 ký tự<br>• **256 (max+1):** RFC email 256 ký tự | • Rỗng: 422 Unprocessable<br>• <= 255: 200 OK<br>• 256: 422 Unprocessable |
| **`currency` (Preferences)** | Kích thước đúng 3 ký tự | • **2 ký tự (size-1):** `"US"`<br>• **3 ký tự (size):** `"USD"` | `"VND"`, `"EUR"`, `"GBP"` | • **4 ký tự (size+1):** `"USDD"` | • 2, 4: 422 Unprocessable<br>• 3 (trong enum): 200 OK |

---

## 4. Báo cáo Chi tiết Đo đạc Code Coverage (Xdebug v3.4.1)

Sau khi bổ sung các automated test cases cho các web controller, policy và trait, kết quả đo đạc trực tiếp qua Xdebug trên tất cả các file nguồn thuộc phạm vi 3 module đạt **100% Code Coverage**:

| STT | File nguồn đối tượng | Lớp / Namespace | Phương thức / Logic đã bao phủ | Tỷ lệ Coverage |
|:---:|:---|:---|:---|:---:|
| 1 | `app/Http/Controllers/Api/V1/Admin/RoleController.php` | `RoleController` | `index`, `options`, `store`, `show`, `update`, `destroy` | **100.0%** |
| 2 | `app/Http/Controllers/Api/V1/Admin/PermissionController.php` | `PermissionController` | `index`, `options`, `store`, `show`, `update`, `destroy` | **100.0%** |
| 3 | `app/Http/Controllers/Api/V1/User/SettingsController.php` | `SettingsController` | `show`, `updateProfile`, `updatePreferences` | **100.0%** |
| 4 | `app/Http/Controllers/Admin/RoleController.php` | `RoleController` (Web) | `index`, `create`, `store`, `edit`, `update`, `destroy` | **100.0%** |
| 5 | `app/Http/Controllers/Admin/PermissionController.php` | `PermissionController` (Web) | `index`, `create`, `store`, `edit`, `update`, `destroy` | **100.0%** |
| 6 | `app/Http/Controllers/User/UserSettingsController.php` | `UserSettingsController` (Web) | `__invoke` (render trang cài đặt và tùy chọn tiền tệ) | **100.0%** |
| 7 | `app/Http/Controllers/User/UserProfileController.php` | `UserProfileController` (Web) | `__invoke` (cập nhật hồ sơ web, reset email verification) | **100.0%** |
| 8 | `app/Http/Controllers/User/UserPreferenceController.php` | `UserPreferenceController` (Web) | `__invoke` (cập nhật tiền tệ web, uppercase currency) | **100.0%** |
| 9 | `app/Services/Admin/AdminRoleService.php` | `AdminRoleService` | `paginate`, `options`, `create`, `find`, `update`, `delete` | **100.0%** |
| 10 | `app/Services/Admin/AdminPermissionService.php` | `AdminPermissionService` | `paginate`, `options`, `create`, `find`, `update`, `delete` | **100.0%** |
| 11 | `app/Services/User/UserSettingsService.php` | `UserSettingsService` | `data`, `updateProfile`, `updateCurrency` | **100.0%** |
| 12 | `app/Policies/RolePolicy.php` | `RolePolicy` | `viewAny`, `view`, `create`, `update`, `delete`, `restore`, `forceDelete` | **100.0%** |
| 13 | `app/Policies/PermissionPolicy.php` | `PermissionPolicy` | `viewAny`, `view`, `create`, `update`, `delete`, `restore`, `forceDelete` | **100.0%** |
| 14 | `app/Http/Requests/Api/V1/Admin/StoreRoleRequest.php` | `StoreRoleRequest` | `authorize`, `rules`, `messages` | **100.0%** |
| 15 | `app/Http/Requests/Api/V1/Admin/UpdateRoleRequest.php` | `UpdateRoleRequest` | `authorize`, `rules`, `messages` | **100.0%** |
| 16 | `app/Http/Requests/Api/V1/Admin/RoleIndexRequest.php` | `RoleIndexRequest` | `authorize`, `rules`, `messages` | **100.0%** |
| 17 | `app/Http/Requests/Api/V1/Admin/StorePermissionRequest.php` | `StorePermissionRequest` | `authorize`, `rules`, `messages` | **100.0%** |
| 18 | `app/Http/Requests/Api/V1/Admin/UpdatePermissionRequest.php` | `UpdatePermissionRequest` | `authorize`, `rules`, `messages` | **100.0%** |
| 19 | `app/Http/Requests/Api/V1/Admin/PermissionIndexRequest.php` | `PermissionIndexRequest` | `authorize`, `rules`, `messages` | **100.0%** |
| 20 | `app/Http/Requests/User/UserProfileUpdateRequest.php` | `UserProfileUpdateRequest` | `authorize`, `rules`, `messages` | **100.0%** |
| 21 | `app/Http/Requests/User/UserPreferenceUpdateRequest.php` | `UserPreferenceUpdateRequest` | `authorize`, `rules`, `messages` | **100.0%** |
| 22 | `app/Concerns/ProfileValidationRules.php` | Trait `ProfileValidationRules` | `profileRules` ($userId null và not null), `nameRules`, `emailRules` | **100.0%** |
| 23 | `app/Http/Resources/Api/V1/Admin/RoleResource.php` | `RoleResource` | Chuyển đổi resource Role, nạp permissions | **100.0%** |
| 24 | `app/Http/Resources/Api/V1/Admin/PermissionResource.php` | `PermissionResource` | Chuyển đổi resource Permission, nạp role count | **100.0%** |
| 25 | `app/Http/Resources/Api/V1/UserSettingsResource.php` | `UserSettingsResource` | Chuyển đổi resource Profile, Preferences, Options | **100.0%** |

---

## 5. Danh mục Chi tiết Bộ Test Case (Test Case Catalog — 236 Cases)

Mỗi test case dưới đây được gán mã định danh duy nhất, ghi rõ kỹ thuật kiểm thử, dữ liệu đầu vào, kết quả mong đợi, kết quả thực tế và trạng thái thực thi.

### 5.1. Module Admin Roles (99 Test Cases)

#### Data-Driven Tests (Shared Execution Data) — 52 Cases:
| Test Case ID | Scenario / Description | Input / Test Data | Expected Result | Actual Result | Technique | Status |
|---|---|---|---|---|:---:|:---:|
| `ADM-ROLE-CREATE-BVA-001` | Tạo vai trò với name độ dài tối thiểu biên dưới (1 ký tự) | `name="a"`, `permissions=[@permission.id]` | HTTP 201 Created, lưu DB guard `web` | 201 Created, DB matched | BVA | **PASSED** |
| `ADM-ROLE-CREATE-BVA-002` | Tạo vai trò với name độ dài biên dưới + 1 (2 ký tự) | `name="aa"`, `permissions=[@permission.id]` | HTTP 201 Created, lưu DB guard `web` | 201 Created, DB matched | BVA | **PASSED** |
| `ADM-ROLE-CREATE-BVA-003` | Tạo vai trò với name độ dài danh nghĩa (128 ký tự) | `name="a"*128`, `permissions=[@permission.id]` | HTTP 201 Created, lưu DB guard `web` | 201 Created, DB matched | BVA | **PASSED** |
| `ADM-ROLE-CREATE-BVA-004` | Tạo vai trò với name độ dài biên trên - 1 (254 ký tự) | `name="a"*254`, `permissions=[@permission.id]` | HTTP 201 Created, lưu DB guard `web` | 201 Created, DB matched | BVA | **PASSED** |
| `ADM-ROLE-CREATE-BVA-005` | Tạo vai trò với name độ dài tối đa biên trên (255 ký tự) | `name="a"*255`, `permissions=[@permission.id]` | HTTP 201 Created, lưu DB guard `web` | 201 Created, DB matched | BVA | **PASSED** |
| `ADM-ROLE-CREATE-RBVA-006` | Tạo vai trò thiếu trường name | `name` absent, `permissions=[@permission.id]` | HTTP 422, validation error `name` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-ROLE-CREATE-RBVA-007` | Tạo vai trò với name vượt biên trên + 1 (256 ký tự) | `name="a"*256`, `permissions=[@permission.id]` | HTTP 422, validation error `name` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-ROLE-CREATE-EP-008` | Tạo vai trò với name là chuỗi rỗng | `name=""`, `permissions=[@permission.id]` | HTTP 422, validation error `name` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-ROLE-CREATE-BUS-009` | Tạo vai trò và liên kết permission pivot | `name="shared-role-with-perm"`, `permissions=[@permission.id]` | HTTP 201, role có quyền gán tương ứng | 201 Created, pivot matched | BUS | **PASSED** |
| `ADM-ROLE-CREATE-EP-010` | Tạo vai trò trùng tên trên guard web | `name="shared-duplicate-role"` (đã có trong DB) | HTTP 422, validation error `name` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-ROLE-CREATE-EP-011` | Tạo vai trò với ID quyền không tồn tại | `permissions=[999999999]` | HTTP 422, error `permissions.0` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-ROLE-CREATE-EP-012` | Tạo vai trò với danh sách ID quyền bị trùng lặp | `permissions=[@perm.id, @perm.id]` | HTTP 422, error `permissions.1` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-ROLE-CREATE-EP-013` | Tạo vai trò với quyền thuộc guard khác (api) | `permissions=[@wrong_guard_perm.id]` | HTTP 422, error `permissions.0` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-ROLE-CREATE-RBAC-014` | Khách vãng lai (guest) gọi API tạo role | Guest, payload hợp lệ | HTTP 401 Unauthorized | 401 Unauthorized | RBAC | **PASSED** |
| `ADM-ROLE-CREATE-RBAC-015` | Người dùng user gọi API tạo role | User, payload hợp lệ | HTTP 403 Forbidden | 403 Forbidden | RBAC | **PASSED** |
| `ADM-ROLE-INDEX-BVA-001` | Tìm kiếm role với search độ dài biên dưới (0 ký tự) | `search=""` | HTTP 200 OK, trả toàn bộ danh sách | 200 OK, full list | BVA | **PASSED** |
| `ADM-ROLE-INDEX-BVA-002` | Tìm kiếm role với search độ dài 1 ký tự | `search="a"` | HTTP 200 OK, trả kết quả lọc | 200 OK, filtered | BVA | **PASSED** |
| `ADM-ROLE-INDEX-BVA-003` | Tìm kiếm role với search độ dài danh nghĩa (128 ký tự) | `search="a"*128` | HTTP 200 OK, trả kết quả lọc | 200 OK, empty/filtered | BVA | **PASSED** |
| `ADM-ROLE-INDEX-BVA-004` | Tìm kiếm role với search độ dài biên trên - 1 (254 ký tự) | `search="a"*254` | HTTP 200 OK, kết quả tìm kiếm | 200 OK, matched | BVA | **PASSED** |
| `ADM-ROLE-INDEX-BVA-005` | Tìm kiếm role với search độ dài tối đa biên trên (255 ký tự) | `search="a"*255` | HTTP 200 OK, kết quả tìm kiếm | 200 OK, matched | BVA | **PASSED** |
| `ADM-ROLE-INDEX-RBVA-006` | Tìm kiếm role với search vượt biên trên + 1 (256 ký tự) | `search="a"*256` | HTTP 422, validation error `search` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-ROLE-INDEX-BVA-007` | Phân trang role với per_page biên dưới (1) | `per_page=1` | HTTP 200 OK, meta per_page = 1 | 200 OK, matched | BVA | **PASSED** |
| `ADM-ROLE-INDEX-BVA-008` | Phân trang role với per_page biên dưới + 1 (2) | `per_page=2` | HTTP 200 OK, meta per_page = 2 | 200 OK, matched | BVA | **PASSED** |
| `ADM-ROLE-INDEX-BVA-009` | Phân trang role với per_page danh nghĩa (50) | `per_page=50` | HTTP 200 OK, meta per_page = 50 | 200 OK, matched | BVA | **PASSED** |
| `ADM-ROLE-INDEX-BVA-010` | Phân trang role với per_page biên trên - 1 (99) | `per_page=99` | HTTP 200 OK, meta per_page = 99 | 200 OK, matched | BVA | **PASSED** |
| `ADM-ROLE-INDEX-BVA-011` | Phân trang role với per_page biên trên (100) | `per_page=100` | HTTP 200 OK, meta per_page = 100 | 200 OK, matched | BVA | **PASSED** |
| `ADM-ROLE-INDEX-RBVA-012` | Phân trang role với per_page dưới biên (0) | `per_page=0` | HTTP 422, validation error `per_page` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-ROLE-INDEX-RBVA-013` | Phân trang role với per_page vượt biên trên (101) | `per_page=101` | HTTP 422, validation error `per_page` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-ROLE-INDEX-EP-014` | Tìm kiếm role trả về đúng 1 bản ghi khớp | `search="shared-match-role"` | HTTP 200 OK, data length = 1 | 200 OK, matched | EP | **PASSED** |
| `ADM-ROLE-INDEX-EP-015` | Lọc role với cột sort không hợp lệ | `sort="invalid_field"` | HTTP 422, validation error `sort` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-ROLE-INDEX-RBAC-016` | Guest gọi API lấy danh sách role | Guest | HTTP 401 Unauthorized | 401 Unauthorized | RBAC | **PASSED** |
| `ADM-ROLE-INDEX-RBAC-017` | User gọi API lấy danh sách role | User | HTTP 403 Forbidden | 403 Forbidden | RBAC | **PASSED** |
| `ADM-ROLE-UPDATE-BVA-001` | Cập nhật role với name độ dài biên dưới (1 ký tự) | `name="b"`, `id=@role.id` | HTTP 200 OK, tên cập nhật thành "b" | 200 OK, DB updated | BVA | **PASSED** |
| `ADM-ROLE-UPDATE-BVA-002` | Cập nhật role với name độ dài 2 ký tự | `name="bb"`, `id=@role.id` | HTTP 200 OK, tên cập nhật | 200 OK, DB updated | BVA | **PASSED** |
| `ADM-ROLE-UPDATE-BVA-003` | Cập nhật role với name độ dài danh nghĩa (128 ký tự) | `name="b"*128`, `id=@role.id` | HTTP 200 OK, tên cập nhật | 200 OK, DB updated | BVA | **PASSED** |
| `ADM-ROLE-UPDATE-BVA-004` | Cập nhật role với name độ dài 254 ký tự | `name="b"*254`, `id=@role.id` | HTTP 200 OK, tên cập nhật | 200 OK, DB updated | BVA | **PASSED** |
| `ADM-ROLE-UPDATE-BVA-005` | Cập nhật role với name độ dài tối đa 255 ký tự | `name="b"*255`, `id=@role.id` | HTTP 200 OK, tên cập nhật | 200 OK, DB updated | BVA | **PASSED** |
| `ADM-ROLE-UPDATE-RBVA-006` | Cập nhật role thiếu trường name | `name` absent, `id=@role.id` | HTTP 422, validation error `name` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-ROLE-UPDATE-RBVA-007` | Cập nhật role với name vượt biên 256 ký tự | `name="b"*256`, `id=@role.id` | HTTP 422, validation error `name` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-ROLE-UPDATE-BUS-008` | Cập nhật role và thay thế toàn bộ permissions | `permissions=[@permission.id]` | HTTP 200 OK, permissions đồng bộ mới | 200 OK, permissions synced | BUS | **PASSED** |
| `ADM-ROLE-UPDATE-EP-009` | Cập nhật role trùng tên với role khác trong DB | `name="shared-duplicate-role"` | HTTP 422, validation error `name` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-ROLE-UPDATE-EP-010` | Cập nhật role với ID quyền không tồn tại | `permissions=[999999999]` | HTTP 422, error `permissions.0` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-ROLE-UPDATE-EP-011` | Cập nhật role với quyền bị trùng lặp | `permissions=[@perm.id, @perm.id]` | HTTP 422, error `permissions.1` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-ROLE-UPDATE-EP-012` | Cập nhật role với quyền thuộc guard khác | `permissions=[@wrong_guard_perm.id]` | HTTP 422, error `permissions.0` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-ROLE-UPDATE-EP-013` | Cập nhật role với ID role không tồn tại | `id=999999999` | HTTP 404 Not Found | 404 Not Found | EP | **PASSED** |
| `ADM-ROLE-UPDATE-RBAC-014` | Guest gọi API cập nhật role | Guest | HTTP 401 Unauthorized | 401 Unauthorized | RBAC | **PASSED** |
| `ADM-ROLE-UPDATE-RBAC-015` | User gọi API cập nhật role | User | HTTP 403 Forbidden | 403 Forbidden | RBAC | **PASSED** |
| `ADM-ROLE-DELETE-BUS-001` | Xóa một vai trò thông thường | `id=@role.id` | HTTP 200 OK, bản ghi bị xóa khỏi DB | 200 OK, deleted | BUS | **PASSED** |
| `ADM-ROLE-DELETE-BUS-002` | Chặn xóa vai trò hệ thống `admin` | `id=@admin_role.id` | HTTP 403 Forbidden, bản ghi giữ nguyên | 403 Forbidden, preserved | BUS | **PASSED** |
| `ADM-ROLE-DELETE-BUS-003` | Chặn xóa vai trò hệ thống `super-admin` | `id=@super_admin_role.id` | HTTP 403 Forbidden, bản ghi giữ nguyên | 403 Forbidden, preserved | BUS | **PASSED** |
| `ADM-ROLE-DELETE-RBAC-004` | Guest gọi API xóa role | Guest | HTTP 401 Unauthorized | 401 Unauthorized | RBAC | **PASSED** |
| `ADM-ROLE-DELETE-RBAC-005` | User gọi API xóa role | User | HTTP 403 Forbidden | 403 Forbidden | RBAC | **PASSED** |

#### Dedicated Feature & Policy Tests — 47 Cases:
- `CreateRoleTest`: 6 cases (Tạo role kèm permission, guest bị chặn 401, user bị chặn 403, validate tên rỗng & trùng lặp permission, chặn trùng tên guard web, validate permissions phải là mảng và phần tử số nguyên).
- `UpdateRoleTest`: 7 cases (Admin cập nhật và sync permissions, guest 401, user 403, bỏ qua permissions giữ nguyên quyền, mảng permissions rỗng gỡ hết quyền, cập nhật role không tồn tại 404, validate permissions kiểu mảng và phần tử nguyên).
- `DeleteRoleTest`: 6 cases (Admin xóa role thường, chặn xóa role `admin`, chặn xóa role `super-admin`, guest 401, user 403, xóa role không tồn tại 404).
- `IndexRoleTest`: 6 cases (Admin xem danh sách role, guest 401, user 403, tìm kiếm sắp xếp phân trang, validate các tham số sort/direction/per_page, BVA cho tham số `page` với `page=0` lỗi 422 và `page=1` pass 200).
- `ShowRoleTest`: 4 cases (Admin xem chi tiết role kèm permissions, guest 401, user 403, xem role không tồn tại 404).
- `OptionsRoleTest`: 3 cases (Admin lấy danh sách options role, guest 401, user 403).
- `WebRoleControllerTest`: 7 cases (Web admin xem danh sách qua Inertia kèm search/pagination, xem form create, submit tạo role mới qua web, validate trùng tên web, xem form edit, submit cập nhật role web, xóa role web).
- `RolePolicyTest`: 2 cases (Kiểm thử 100% các method `viewAny`, `view`, `create`, `update`, `delete`, `restore`, `forceDelete` cho admin và user; kiểm tra rule cấm xóa `admin` và `super-admin`).
- `RoleControllerTest` (API V1): 6 cases (List, options, create, show, update, delete qua API V1 Controller).

---

### 5.2. Module Admin Permissions (84 Test Cases)

#### Data-Driven Tests (Shared Execution Data) — 43 Cases:
| Test Case ID | Scenario / Description | Input / Test Data | Expected Result | Actual Result | Technique | Status |
|---|---|---|---|---|:---:|:---:|
| `ADM-PERM-CREATE-BVA-001` | Tạo quyền với name độ dài biên dưới (1 ký tự) | `name="a"` | HTTP 201 Created, guard `web` | 201 Created, DB matched | BVA | **PASSED** |
| `ADM-PERM-CREATE-BVA-002` | Tạo quyền với name độ dài 2 ký tự | `name="aa"` | HTTP 201 Created | 201 Created, DB matched | BVA | **PASSED** |
| `ADM-PERM-CREATE-BVA-003` | Tạo quyền với name độ dài danh nghĩa (128 ký tự) | `name="a"*128` | HTTP 201 Created | 201 Created, DB matched | BVA | **PASSED** |
| `ADM-PERM-CREATE-BVA-004` | Tạo quyền với name độ dài 254 ký tự | `name="a"*254` | HTTP 201 Created | 201 Created, DB matched | BVA | **PASSED** |
| `ADM-PERM-CREATE-BVA-005` | Tạo quyền với name độ dài tối đa 255 ký tự | `name="a"*255` | HTTP 201 Created | 201 Created, DB matched | BVA | **PASSED** |
| `ADM-PERM-CREATE-RBVA-006` | Tạo quyền thiếu trường name | `name` absent | HTTP 422, validation error `name` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-PERM-CREATE-RBVA-007` | Tạo quyền với name vượt biên trên (256 ký tự) | `name="a"*256` | HTTP 422, validation error `name` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-PERM-CREATE-EP-008` | Tạo quyền với name là chuỗi rỗng | `name=""` | HTTP 422, validation error `name` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-PERM-CREATE-EP-009` | Tạo quyền trùng tên trên guard web | `name="shared-duplicate-permission"` | HTTP 422, validation error `name` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-PERM-CREATE-RBAC-010` | Guest gọi API tạo quyền | Guest | HTTP 401 Unauthorized | 401 Unauthorized | RBAC | **PASSED** |
| `ADM-PERM-CREATE-RBAC-011` | User gọi API tạo quyền | User | HTTP 403 Forbidden | 403 Forbidden | RBAC | **PASSED** |
| `ADM-PERM-INDEX-BVA-001` | Tìm kiếm quyền với search 0 ký tự | `search=""` | HTTP 200 OK | 200 OK, full list | BVA | **PASSED** |
| `ADM-PERM-INDEX-BVA-002` | Tìm kiếm quyền với search 1 ký tự | `search="a"` | HTTP 200 OK | 200 OK, filtered | BVA | **PASSED** |
| `ADM-PERM-INDEX-BVA-003` | Tìm kiếm quyền với search danh nghĩa (128 ký tự) | `search="a"*128` | HTTP 200 OK | 200 OK, matched | BVA | **PASSED** |
| `ADM-PERM-INDEX-BVA-004` | Tìm kiếm quyền với search 254 ký tự | `search="a"*254` | HTTP 200 OK | 200 OK, matched | BVA | **PASSED** |
| `ADM-PERM-INDEX-BVA-005` | Tìm kiếm quyền với search tối đa 255 ký tự | `search="a"*255` | HTTP 200 OK | 200 OK, matched | BVA | **PASSED** |
| `ADM-PERM-INDEX-RBVA-006` | Tìm kiếm quyền với search vượt biên 256 ký tự | `search="a"*256` | HTTP 422, validation error `search` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-PERM-INDEX-BVA-007` | Phân trang quyền với per_page biên dưới (1) | `per_page=1` | HTTP 200 OK, meta per_page = 1 | 200 OK, matched | BVA | **PASSED** |
| `ADM-PERM-INDEX-BVA-008` | Phân trang quyền với per_page biên dưới + 1 (2) | `per_page=2` | HTTP 200 OK, meta per_page = 2 | 200 OK, matched | BVA | **PASSED** |
| `ADM-PERM-INDEX-BVA-009` | Phân trang quyền với per_page danh nghĩa (50) | `per_page=50` | HTTP 200 OK, meta per_page = 50 | 200 OK, matched | BVA | **PASSED** |
| `ADM-PERM-INDEX-BVA-010` | Phân trang quyền với per_page 99 | `per_page=99` | HTTP 200 OK, meta per_page = 99 | 200 OK, matched | BVA | **PASSED** |
| `ADM-PERM-INDEX-BVA-011` | Phân trang quyền với per_page tối đa (100) | `per_page=100` | HTTP 200 OK, meta per_page = 100 | 200 OK, matched | BVA | **PASSED** |
| `ADM-PERM-INDEX-RBVA-012` | Phân trang quyền với per_page = 0 | `per_page=0` | HTTP 422, validation error `per_page` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-PERM-INDEX-RBVA-013` | Phân trang quyền với per_page = 101 | `per_page=101` | HTTP 422, validation error `per_page` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-PERM-INDEX-EP-014` | Tìm kiếm quyền khớp 1 bản ghi | `search="shared-match-permission"` | HTTP 200 OK, data length = 1 | 200 OK, matched | EP | **PASSED** |
| `ADM-PERM-INDEX-EP-015` | Sắp xếp quyền với cột không hợp lệ | `sort="bad_column"` | HTTP 422, validation error `sort` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-PERM-INDEX-RBAC-016` | Guest gọi API danh sách quyền | Guest | HTTP 401 Unauthorized | 401 Unauthorized | RBAC | **PASSED** |
| `ADM-PERM-INDEX-RBAC-017` | User gọi API danh sách quyền | User | HTTP 403 Forbidden | 403 Forbidden | RBAC | **PASSED** |
| `ADM-PERM-UPDATE-BVA-001` | Cập nhật quyền với name biên dưới (1 ký tự) | `name="c"`, `id=@permission.id` | HTTP 200 OK, DB updated | 200 OK, DB updated | BVA | **PASSED** |
| `ADM-PERM-UPDATE-BVA-002` | Cập nhật quyền với name 2 ký tự | `name="cc"`, `id=@permission.id` | HTTP 200 OK, DB updated | 200 OK, DB updated | BVA | **PASSED** |
| `ADM-PERM-UPDATE-BVA-003` | Cập nhật quyền với name danh nghĩa 128 ký tự | `name="c"*128`, `id=@permission.id` | HTTP 200 OK, DB updated | 200 OK, DB updated | BVA | **PASSED** |
| `ADM-PERM-UPDATE-BVA-004` | Cập nhật quyền với name 254 ký tự | `name="c"*254`, `id=@permission.id` | HTTP 200 OK, DB updated | 200 OK, DB updated | BVA | **PASSED** |
| `ADM-PERM-UPDATE-BVA-005` | Cập nhật quyền với name tối đa 255 ký tự | `name="c"*255`, `id=@permission.id` | HTTP 200 OK, DB updated | 200 OK, DB updated | BVA | **PASSED** |
| `ADM-PERM-UPDATE-RBVA-006` | Cập nhật quyền thiếu trường name | `name` absent, `id=@permission.id` | HTTP 422, validation error `name` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-PERM-UPDATE-RBVA-007` | Cập nhật quyền với name vượt biên 256 ký tự | `name="c"*256`, `id=@permission.id` | HTTP 422, validation error `name` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `ADM-PERM-UPDATE-EP-008` | Cập nhật quyền trùng tên quyền khác | `name="shared-duplicate-permission"` | HTTP 422, validation error `name` | 422 Unprocessable, error matched | EP | **PASSED** |
| `ADM-PERM-UPDATE-EP-009` | Cập nhật quyền với ID không tồn tại | `id=999999999` | HTTP 404 Not Found | 404 Not Found | EP | **PASSED** |
| `ADM-PERM-UPDATE-RBAC-010` | Guest gọi API cập nhật quyền | Guest | HTTP 401 Unauthorized | 401 Unauthorized | RBAC | **PASSED** |
| `ADM-PERM-UPDATE-RBAC-011` | User gọi API cập nhật quyền | User | HTTP 403 Forbidden | 403 Forbidden | RBAC | **PASSED** |
| `ADM-PERM-DELETE-BUS-001` | Xóa quyền tồn tại hợp lệ | `id=@permission.id` | HTTP 200 OK, xóa khỏi DB | 200 OK, deleted | BUS | **PASSED** |
| `ADM-PERM-DELETE-RBAC-002` | Guest gọi API xóa quyền | Guest | HTTP 401 Unauthorized | 401 Unauthorized | RBAC | **PASSED** |
| `ADM-PERM-DELETE-RBAC-003` | User gọi API xóa quyền | User | HTTP 403 Forbidden | 403 Forbidden | RBAC | **PASSED** |
| `ADM-PERM-DELETE-EP-004` | Xóa quyền với ID không tồn tại | `id=999999999` | HTTP 404 Not Found | 404 Not Found | EP | **PASSED** |

#### Dedicated Feature & Policy Tests — 41 Cases:
- `CreatePermissionTest`: 5 cases (Admin tạo permission, validate trùng tên, guest 401, user 403, validate bắt buộc tên).
- `UpdatePermissionTest`: 5 cases (Admin cập nhật permission, validate trùng tên, guest 401, user 403, cập nhật quyền không tồn tại 404).
- `DeletePermissionTest`: 4 cases (Admin xóa permission, guest 401, user 403, xóa quyền không tồn tại 404).
- `IndexPermissionTest`: 6 cases (Admin xem danh sách, guest 401, user 403, tìm kiếm phân trang sắp xếp, validate query params, BVA cho tham số `page` với `page=0` lỗi 422 và `page=1` pass 200).
- `ShowPermissionTest`: 4 cases (Admin xem chi tiết, guest 401, user 403, xem quyền không tồn tại 404).
- `OptionsPermissionTest`: 3 cases (Admin lấy options, guest 401, user 403).
- `WebPermissionControllerTest`: 7 cases (Web admin xem danh sách qua Inertia kèm search/pagination, xem form create, submit tạo quyền mới qua web, validate trùng tên web, xem form edit, submit cập nhật quyền web, xóa quyền web).
- `PermissionPolicyTest`: 1 case (Kiểm thử 100% các method `viewAny`, `view`, `create`, `update`, `delete`, `restore`, `forceDelete` cho admin và user).
- `PermissionControllerTest` (API V1): 6 cases (List, options, create, show, update, delete qua API V1 Controller).

---

### 5.3. Module User Settings (53 Test Cases)

#### Data-Driven Tests (Shared Execution Data) — 26 Cases:
| Test Case ID | Scenario / Description | Input / Test Data | Expected Result | Actual Result | Technique | Status |
|---|---|---|---|---|:---:|:---:|
| `USR-SET-PROFILE-UPDATE-BVA-001` | Cập nhật hồ sơ với name biên dưới (1 ký tự) | `name="a"`, `email="valid@example.com"` | HTTP 200 OK, name="a" | 200 OK, DB updated | BVA | **PASSED** |
| `USR-SET-PROFILE-UPDATE-BVA-002` | Cập nhật hồ sơ với name 2 ký tự | `name="aa"`, `email="valid@example.com"` | HTTP 200 OK, name="aa" | 200 OK, DB updated | BVA | **PASSED** |
| `USR-SET-PROFILE-UPDATE-BVA-003` | Cập nhật hồ sơ với name danh nghĩa 128 ký tự | `name="a"*128`, `email="valid@example.com"` | HTTP 200 OK | 200 OK, DB updated | BVA | **PASSED** |
| `USR-SET-PROFILE-UPDATE-BVA-004` | Cập nhật hồ sơ với name 254 ký tự | `name="a"*254`, `email="valid@example.com"` | HTTP 200 OK | 200 OK, DB updated | BVA | **PASSED** |
| `USR-SET-PROFILE-UPDATE-BVA-005` | Cập nhật hồ sơ với name tối đa 255 ký tự | `name="a"*255`, `email="valid@example.com"` | HTTP 200 OK | 200 OK, DB updated | BVA | **PASSED** |
| `USR-SET-PROFILE-UPDATE-RBVA-006` | Cập nhật hồ sơ thiếu trường name | `name` absent, `email="valid@example.com"` | HTTP 422, error `name` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `USR-SET-PROFILE-UPDATE-RBVA-007` | Cập nhật hồ sơ với name vượt biên 256 ký tự | `name="a"*256`, `email="valid@example.com"` | HTTP 422, error `name` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `USR-SET-PROFILE-UPDATE-BVA-008` | Cập nhật email định dạng chuẩn danh nghĩa | `email="nominal@example.com"` | HTTP 200 OK, email updated | 200 OK, DB updated | BVA | **PASSED** |
| `USR-SET-PROFILE-UPDATE-BVA-009` | Cập nhật email độ dài 254 ký tự | `email` RFC 254 chars | HTTP 200 OK, email updated | 200 OK, DB updated | BVA | **PASSED** |
| `USR-SET-PROFILE-UPDATE-BVA-010` | Cập nhật email độ dài tối đa 255 ký tự | `email` RFC 255 chars | HTTP 200 OK, email updated | 200 OK, DB updated | BVA | **PASSED** |
| `USR-SET-PROFILE-UPDATE-EP-011` | Cập nhật hồ sơ thiếu trường email | `email` absent | HTTP 422, error `email` | 422 Unprocessable, error matched | EP | **PASSED** |
| `USR-SET-PROFILE-UPDATE-EP-012` | Cập nhật hồ sơ với email sai định dạng | `email="not-an-email"` | HTTP 422, error `email` | 422 Unprocessable, error matched | EP | **PASSED** |
| `USR-SET-PROFILE-UPDATE-RBVA-013` | Cập nhật hồ sơ với email vượt biên 256 ký tự | `email` RFC 256 chars | HTTP 422, error `email` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `USR-SET-PROFILE-UPDATE-BUS-014` | Cập nhật giữ nguyên email bảo toàn email_verified_at | `email` giữ nguyên email hiện tại | HTTP 200 OK, timestamp không đổi | 200 OK, verified_at preserved | BUS | **PASSED** |
| `USR-SET-PROFILE-UPDATE-BUS-015` | Cập nhật email trùng với tài khoản người dùng khác | `email="existing@example.test"` | HTTP 422, error `email` | 422 Unprocessable, error matched | BUS | **PASSED** |
| `USR-SET-PROFILE-UPDATE-RBAC-016` | Guest gọi API cập nhật hồ sơ | Guest | HTTP 401 Unauthorized | 401 Unauthorized | RBAC | **PASSED** |
| `USR-SET-PREF-UPDATE-EP-001` | Cập nhật tùy chọn tiền tệ sang VND | `currency="VND"` | HTTP 200 OK, currency = VND | 200 OK, currency=VND | EP | **PASSED** |
| `USR-SET-PREF-UPDATE-EP-002` | Cập nhật tùy chọn tiền tệ sang USD | `currency="USD"` | HTTP 200 OK, currency = USD | 200 OK, currency=USD | EP | **PASSED** |
| `USR-SET-PREF-UPDATE-EP-003` | Cập nhật tùy chọn tiền tệ sang EUR | `currency="EUR"` | HTTP 200 OK, currency = EUR | 200 OK, currency=EUR | EP | **PASSED** |
| `USR-SET-PREF-UPDATE-EP-004` | Cập nhật tùy chọn tiền tệ sang GBP | `currency="GBP"` | HTTP 200 OK, currency = GBP | 200 OK, currency=GBP | EP | **PASSED** |
| `USR-SET-PREF-UPDATE-RBVA-005` | Cập nhật tiền tệ dưới biên độ dài (2 ký tự) | `currency="US"` | HTTP 422, error `currency` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `USR-SET-PREF-UPDATE-RBVA-006` | Cập nhật tiền tệ vượt biên độ dài (4 ký tự) | `currency="USDD"` | HTTP 422, error `currency` | 422 Unprocessable, error matched | RBVA | **PASSED** |
| `USR-SET-PREF-UPDATE-EP-007` | Cập nhật mã tiền tệ ngoài danh mục cho phép | `currency="JPY"` | HTTP 422, error `currency` | 422 Unprocessable, error matched | EP | **PASSED** |
| `USR-SET-PREF-UPDATE-BUS-008` | Cập nhật tiền tệ liên tiếp có tính chất Idempotent | `currency="USD"` lặp lại | HTTP 200 OK, dữ liệu không đổi | 200 OK, idempotent | BUS | **PASSED** |
| `USR-SET-PREF-UPDATE-RBAC-009` | Guest gọi API cập nhật tiền tệ | Guest | HTTP 401 Unauthorized | 401 Unauthorized | RBAC | **PASSED** |
| `USR-SET-PREF-UPDATE-RBAC-010` | Admin gọi endpoint tiền tệ dành riêng cho user | Admin | HTTP 403 Forbidden | 403 Forbidden | RBAC | **PASSED** |

#### Dedicated Feature & Trait Tests — 27 Cases:
- `ShowSettingsTest`: 5 cases (User xem settings cá nhân, guest 401, mặc định VND khi chưa đặt, admin bị chặn 403, không lộ trường nhạy cảm `password`/`remember_token`/`two_factor_secret`).
- `UpdatePreferencesTest`: 6 cases (User đổi tiền tệ, guest 401, admin 403, validate required, validate size 3 ký tự, validate enum cho phép).
- `UpdateProfileTest`: 8 cases (User cập nhật profile, validate name, guest 401, validate required name/email, chặn email trùng user khác, đổi email reset `email_verified_at` về null, giữ nguyên email bảo toàn `email_verified_at`, không lộ trường nhạy cảm trong response).
- `WebUserSettingsControllerTest`: 4 cases (Web user xem trang setting qua Inertia, web user đổi profile và reset verification, web user giữ nguyên email bảo toàn verification, web user cập nhật tiền tệ).
- `SettingsControllerTest` (API V1): 3 cases (Xem settings, update profile, update preferences qua API V1 Controller).
- `ProfileValidationRulesTest`: 1 case (Kiểm thử trait `ProfileValidationRules` cho cả trường hợp truyền `$userId` và trường hợp `$userId === null`).

---

## 6. Lệnh Thực thi Kiểm thử & Đo đạc Coverage

Để tái hiện lại toàn bộ kết quả kiểm thử và đo đạc độ bao phủ trên môi trường local:

```bash
cd backend

# 1. Thực thi toàn bộ 236 test cases của 3 module phụ trách:
php artisan test --compact tests/Feature/Admin/Role tests/Feature/Admin/Permission tests/Feature/User/Settings tests/Feature/Api/V1/Admin/RoleControllerTest.php tests/Feature/Api/V1/Admin/PermissionControllerTest.php tests/Feature/Api/V1/User/SettingsControllerTest.php tests/Unit/Concerns/ProfileValidationRulesTest.php

# 2. Đo đạc Code Coverage thực tế với Xdebug (yêu cầu xdebug.mode=coverage trong php.ini):
php artisan test --coverage --compact tests/Feature/Admin/Role tests/Feature/Admin/Permission tests/Feature/User/Settings tests/Feature/Api/V1/Admin/RoleControllerTest.php tests/Feature/Api/V1/Admin/PermissionControllerTest.php tests/Feature/Api/V1/User/SettingsControllerTest.php tests/Unit/Concerns/ProfileValidationRulesTest.php

# 3. Chạy kiểm tra hợp đồng dữ liệu test:
php artisan test --compact tests/Unit/Support/TestDataTest.php
```

---

## 7. Kết luận

Bộ kiểm thử của 3 module **Admin Roles**, **Admin Permissions**, và **User Settings** đã được hoàn thiện vượt mức yêu cầu:
1. **Phương pháp khoa học:** Áp dụng đầy đủ và chuẩn xác hai kỹ thuật hộp đen cốt lõi là **Equivalence Partitioning (Phân vùng tương đương)** và **Boundary Value Analysis (Phân tích giá trị biên 6 điểm)**.
2. **Quy mô và tính đồng nhất:** Toàn bộ **236 test cases** đều có mã định danh, mô tả chi tiết, phân loại kỹ thuật và được thực thi tự động hóa 100% trên Pest framework. Tỷ lệ pass đạt **100% (236/236)**, không có test failed, skipped hay todo nào còn sót lại.
3. **Chất lượng code coverage:** Đã giải quyết triệt để vấn đề thiếu driver đo đạc bằng cách tích hợp trực tiếp **Xdebug v3.4.1**, chứng minh toàn bộ **25 file nguồn** (Controllers Web & API, Services, Form Requests, Policies, Resources, Traits) thuộc phạm vi 3 module đạt **100.0% Code Coverage**.
