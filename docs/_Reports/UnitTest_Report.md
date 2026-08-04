# BÁO CÁO KIỂM THỬ BACKEND (UNIT/FEATURE TEST)

## 1. Thông tin chung

| Hạng mục | Giá trị |
|---|---|
| Dự án | KCPM |
| Phạm vi | Backend Laravel API |
| Thư mục kiểm thử | [`backend/tests`](../../backend/tests/) |
| Framework kiểm thử | Pest 4.4.1 / PHPUnit 12 — khai báo tại [`backend/composer.json`](../../backend/composer.json) |
| Framework ứng dụng | Laravel 12.53.0 — phiên bản khóa tại [`backend/composer.lock`](../../backend/composer.lock) |
| Phiên bản PHP | PHP 8.4.7 |
| Nhánh kiểm thử | `UnitTest` |
| Commit được kiểm thử | `1716866b419cda58dcdde83cc12e1058379e2a64` |
| Thời điểm thực thi gần nhất | 04/08/2026 20:14:23, múi giờ Asia/Ho_Chi_Minh (UTC+07:00) |
| Lệnh thực thi | `php artisan test --compact` |
| Cấu hình test suite | [`backend/phpunit.xml`](../../backend/phpunit.xml) |
| Cấu hình Pest và test helper | [`backend/tests/Pest.php`](../../backend/tests/Pest.php) |

## 2. Mục tiêu kiểm thử

- Xác nhận các API backend hoạt động đúng theo các luồng nghiệp vụ đã triển khai.
- Kiểm tra xác thực, phân quyền giữa guest, user và admin.
- Kiểm tra CRUD, validation, dữ liệu không tồn tại, dữ liệu trùng lặp và quyền sở hữu dữ liệu.
- Kiểm tra các quy tắc nghiệp vụ chính của budget, transaction, dashboard, menu, role, permission và settings.
- Tạo kết quả regression có thể chạy lại trên môi trường test độc lập.

## 3. Cấu hình và phương pháp

- Test suite sử dụng Pest và Laravel HTTP testing; dependency được khai báo tại [`backend/composer.json`](../../backend/composer.json).
- Feature test dùng trait `RefreshDatabase`, được kích hoạt tại [`backend/tests/Pest.php`](../../backend/tests/Pest.php), bảo đảm trạng thái dữ liệu được làm mới giữa các test.
- Database kiểm thử là SQLite in-memory: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:` theo [`backend/phpunit.xml`](../../backend/phpunit.xml).
- Cache dùng `array`, queue dùng `sync`, session dùng `array`, mail dùng `array` theo [`backend/phpunit.xml`](../../backend/phpunit.xml) để tránh phụ thuộc hạ tầng ngoài.
- Dữ liệu test được khởi tạo bằng model factory và các helper tài khoản `adminUser()` / `regularUser()` trong [`backend/tests/Pest.php`](../../backend/tests/Pest.php).
- Lớp test nền của ứng dụng nằm tại [`backend/tests/TestCase.php`](../../backend/tests/TestCase.php).
- Báo cáo được tổng hợp từ kết quả chạy thật của toàn bộ suite, không suy luận chỉ từ số lượng file.

## 4. Kết quả tổng quan

| Chỉ số | Kết quả |
|---|---:|
| Tổng số file test | 78 |
| Unit test | 1 |
| Feature/API test | 181 |
| Tổng số test case | 182 |
| Tổng số assertion | 671 |
| Passed | 182 |
| Failed | 0 |
| Errors | 0 |
| Skipped | 0 |
| Tỷ lệ pass | **100%** |
| Thời gian chạy gần nhất | 2,00 giây |
| Exit code | 0 |

**Kết quả chung: PASSED** — toàn bộ test case trong phạm vi hiện tại đã chạy thành công.

## 5. Kết quả chi tiết theo module

| Nhóm chức năng | File test | Test case | Kết quả |
|---|---:|---:|---|
| [Unit](../../backend/tests/Unit/) | 1 | 1 | Passed |
| [Smoke test](../../backend/tests/Feature/SmokeTest.php) | 1 | 1 | Passed |
| [Authentication](../../backend/tests/Feature/Auth/) | 6 | 8 | Passed |
| [Public API](../../backend/tests/Feature/Public/) | 2 | 3 | Passed |
| [Admin / Appearance](../../backend/tests/Feature/Admin/Appearance/) | 2 | 2 | Passed |
| [Admin / Budget](../../backend/tests/Feature/Admin/Budget/) | 6 | 37 | Passed |
| [Admin / Category](../../backend/tests/Feature/Admin/Category/) | 5 | 5 | Passed |
| [Admin / Dashboard](../../backend/tests/Feature/Admin/Dashboard/) | 1 | 1 | Passed |
| [Admin / Menu](../../backend/tests/Feature/Admin/Menu/) | 6 | 35 | Passed |
| [Admin / Page](../../backend/tests/Feature/Admin/Page/) | 5 | 5 | Passed |
| [Admin / Permission](../../backend/tests/Feature/Admin/Permission/) | 6 | 9 | Passed |
| [Admin / Role](../../backend/tests/Feature/Admin/Role/) | 6 | 8 | Passed |
| [Admin / Transaction](../../backend/tests/Feature/Admin/Transaction/) | 6 | 6 | Passed |
| [Admin / User](../../backend/tests/Feature/Admin/User/) | 5 | 5 | Passed |
| [API V1 / Admin / PermissionController](../../backend/tests/Feature/Api/V1/Admin/PermissionControllerTest.php) | 1 | 6 | Passed |
| [API V1 / Admin / RoleController](../../backend/tests/Feature/Api/V1/Admin/RoleControllerTest.php) | 1 | 6 | Passed |
| [API V1 / User / SettingsController](../../backend/tests/Feature/Api/V1/User/SettingsControllerTest.php) | 1 | 3 | Passed |
| [User / Budget](../../backend/tests/Feature/User/Budget/) | 2 | 18 | Passed |
| [User / Category](../../backend/tests/Feature/User/Category/) | 5 | 6 | Passed |
| [User / Dashboard](../../backend/tests/Feature/User/Dashboard/) | 1 | 6 | Passed |
| [User / Settings](../../backend/tests/Feature/User/Settings/) | 3 | 5 | Passed |
| [User / Transaction](../../backend/tests/Feature/User/Transaction/) | 2 | 2 | Passed |
| [User / Wallet](../../backend/tests/Feature/User/Wallet/) | 4 | 4 | Passed |
| **Tổng cộng** | **78** | **182** | **Passed** |

## 6. Chi tiết kết quả theo từng file test

Mỗi tên file trong bảng dưới đây là một liên kết trực tiếp tới source test. Số liệu test case được lấy từ JUnit metadata của lần chạy toàn bộ suite gần nhất.

| Nhóm chức năng | File test | Test case | Kết quả |
|---|---|---:|---|
| Admin / Appearance | [ShowAppearanceTest.php](../../backend/tests/Feature/Admin/Appearance/ShowAppearanceTest.php) | 1 | Passed |
| Admin / Appearance | [UpdateAppearanceTest.php](../../backend/tests/Feature/Admin/Appearance/UpdateAppearanceTest.php) | 1 | Passed |
| Admin / Budget | [CreateBudgetTest.php](../../backend/tests/Feature/Admin/Budget/CreateBudgetTest.php) | 10 | Passed |
| Admin / Budget | [DeleteBudgetTest.php](../../backend/tests/Feature/Admin/Budget/DeleteBudgetTest.php) | 4 | Passed |
| Admin / Budget | [IndexBudgetTest.php](../../backend/tests/Feature/Admin/Budget/IndexBudgetTest.php) | 8 | Passed |
| Admin / Budget | [OptionsBudgetTest.php](../../backend/tests/Feature/Admin/Budget/OptionsBudgetTest.php) | 3 | Passed |
| Admin / Budget | [ShowBudgetTest.php](../../backend/tests/Feature/Admin/Budget/ShowBudgetTest.php) | 5 | Passed |
| Admin / Budget | [UpdateBudgetTest.php](../../backend/tests/Feature/Admin/Budget/UpdateBudgetTest.php) | 7 | Passed |
| Admin / Category | [CreateCategoryTest.php](../../backend/tests/Feature/Admin/Category/CreateCategoryTest.php) | 1 | Passed |
| Admin / Category | [DeleteCategoryTest.php](../../backend/tests/Feature/Admin/Category/DeleteCategoryTest.php) | 1 | Passed |
| Admin / Category | [IndexCategoryTest.php](../../backend/tests/Feature/Admin/Category/IndexCategoryTest.php) | 1 | Passed |
| Admin / Category | [ShowCategoryTest.php](../../backend/tests/Feature/Admin/Category/ShowCategoryTest.php) | 1 | Passed |
| Admin / Category | [UpdateCategoryTest.php](../../backend/tests/Feature/Admin/Category/UpdateCategoryTest.php) | 1 | Passed |
| Admin / Dashboard | [ShowDashboardTest.php](../../backend/tests/Feature/Admin/Dashboard/ShowDashboardTest.php) | 1 | Passed |
| Admin / Menu | [CreateMenuTest.php](../../backend/tests/Feature/Admin/Menu/CreateMenuTest.php) | 7 | Passed |
| Admin / Menu | [DeleteMenuTest.php](../../backend/tests/Feature/Admin/Menu/DeleteMenuTest.php) | 5 | Passed |
| Admin / Menu | [IndexMenuTest.php](../../backend/tests/Feature/Admin/Menu/IndexMenuTest.php) | 7 | Passed |
| Admin / Menu | [ParentOptionsMenuTest.php](../../backend/tests/Feature/Admin/Menu/ParentOptionsMenuTest.php) | 5 | Passed |
| Admin / Menu | [ShowMenuTest.php](../../backend/tests/Feature/Admin/Menu/ShowMenuTest.php) | 5 | Passed |
| Admin / Menu | [UpdateMenuTest.php](../../backend/tests/Feature/Admin/Menu/UpdateMenuTest.php) | 6 | Passed |
| Admin / Page | [CreatePageTest.php](../../backend/tests/Feature/Admin/Page/CreatePageTest.php) | 1 | Passed |
| Admin / Page | [DeletePageTest.php](../../backend/tests/Feature/Admin/Page/DeletePageTest.php) | 1 | Passed |
| Admin / Page | [IndexPageTest.php](../../backend/tests/Feature/Admin/Page/IndexPageTest.php) | 1 | Passed |
| Admin / Page | [ShowPageTest.php](../../backend/tests/Feature/Admin/Page/ShowPageTest.php) | 1 | Passed |
| Admin / Page | [UpdatePageTest.php](../../backend/tests/Feature/Admin/Page/UpdatePageTest.php) | 1 | Passed |
| Admin / Permission | [CreatePermissionTest.php](../../backend/tests/Feature/Admin/Permission/CreatePermissionTest.php) | 2 | Passed |
| Admin / Permission | [DeletePermissionTest.php](../../backend/tests/Feature/Admin/Permission/DeletePermissionTest.php) | 1 | Passed |
| Admin / Permission | [IndexPermissionTest.php](../../backend/tests/Feature/Admin/Permission/IndexPermissionTest.php) | 2 | Passed |
| Admin / Permission | [OptionsPermissionTest.php](../../backend/tests/Feature/Admin/Permission/OptionsPermissionTest.php) | 1 | Passed |
| Admin / Permission | [ShowPermissionTest.php](../../backend/tests/Feature/Admin/Permission/ShowPermissionTest.php) | 1 | Passed |
| Admin / Permission | [UpdatePermissionTest.php](../../backend/tests/Feature/Admin/Permission/UpdatePermissionTest.php) | 2 | Passed |
| Admin / Role | [CreateRoleTest.php](../../backend/tests/Feature/Admin/Role/CreateRoleTest.php) | 1 | Passed |
| Admin / Role | [DeleteRoleTest.php](../../backend/tests/Feature/Admin/Role/DeleteRoleTest.php) | 2 | Passed |
| Admin / Role | [IndexRoleTest.php](../../backend/tests/Feature/Admin/Role/IndexRoleTest.php) | 2 | Passed |
| Admin / Role | [OptionsRoleTest.php](../../backend/tests/Feature/Admin/Role/OptionsRoleTest.php) | 1 | Passed |
| Admin / Role | [ShowRoleTest.php](../../backend/tests/Feature/Admin/Role/ShowRoleTest.php) | 1 | Passed |
| Admin / Role | [UpdateRoleTest.php](../../backend/tests/Feature/Admin/Role/UpdateRoleTest.php) | 1 | Passed |
| Admin / Transaction | [CreateTransactionTest.php](../../backend/tests/Feature/Admin/Transaction/CreateTransactionTest.php) | 1 | Passed |
| Admin / Transaction | [DeleteTransactionTest.php](../../backend/tests/Feature/Admin/Transaction/DeleteTransactionTest.php) | 1 | Passed |
| Admin / Transaction | [IndexTransactionTest.php](../../backend/tests/Feature/Admin/Transaction/IndexTransactionTest.php) | 1 | Passed |
| Admin / Transaction | [OptionsTransactionTest.php](../../backend/tests/Feature/Admin/Transaction/OptionsTransactionTest.php) | 1 | Passed |
| Admin / Transaction | [ShowTransactionTest.php](../../backend/tests/Feature/Admin/Transaction/ShowTransactionTest.php) | 1 | Passed |
| Admin / Transaction | [UpdateTransactionTest.php](../../backend/tests/Feature/Admin/Transaction/UpdateTransactionTest.php) | 1 | Passed |
| Admin / User | [CreateUserTest.php](../../backend/tests/Feature/Admin/User/CreateUserTest.php) | 1 | Passed |
| Admin / User | [DeleteUserTest.php](../../backend/tests/Feature/Admin/User/DeleteUserTest.php) | 1 | Passed |
| Admin / User | [IndexUserTest.php](../../backend/tests/Feature/Admin/User/IndexUserTest.php) | 1 | Passed |
| Admin / User | [ShowUserTest.php](../../backend/tests/Feature/Admin/User/ShowUserTest.php) | 1 | Passed |
| Admin / User | [UpdateUserTest.php](../../backend/tests/Feature/Admin/User/UpdateUserTest.php) | 1 | Passed |
| API V1 / Admin | [PermissionControllerTest.php](../../backend/tests/Feature/Api/V1/Admin/PermissionControllerTest.php) | 6 | Passed |
| API V1 / Admin | [RoleControllerTest.php](../../backend/tests/Feature/Api/V1/Admin/RoleControllerTest.php) | 6 | Passed |
| API V1 / User | [SettingsControllerTest.php](../../backend/tests/Feature/Api/V1/User/SettingsControllerTest.php) | 3 | Passed |
| Authentication | [ForgotPasswordTest.php](../../backend/tests/Feature/Auth/ForgotPasswordTest.php) | 1 | Passed |
| Authentication | [LoginTest.php](../../backend/tests/Feature/Auth/LoginTest.php) | 2 | Passed |
| Authentication | [LogoutTest.php](../../backend/tests/Feature/Auth/LogoutTest.php) | 1 | Passed |
| Authentication | [MeTest.php](../../backend/tests/Feature/Auth/MeTest.php) | 2 | Passed |
| Authentication | [RegisterTest.php](../../backend/tests/Feature/Auth/RegisterTest.php) | 1 | Passed |
| Authentication | [ResetPasswordTest.php](../../backend/tests/Feature/Auth/ResetPasswordTest.php) | 1 | Passed |
| Public API | [ConfigurationTest.php](../../backend/tests/Feature/Public/ConfigurationTest.php) | 1 | Passed |
| Public API | [ShowPageTest.php](../../backend/tests/Feature/Public/ShowPageTest.php) | 2 | Passed |
| Smoke test | [SmokeTest.php](../../backend/tests/Feature/SmokeTest.php) | 1 | Passed |
| User / Budget | [CreateBudgetTest.php](../../backend/tests/Feature/User/Budget/CreateBudgetTest.php) | 12 | Passed |
| User / Budget | [IndexBudgetTest.php](../../backend/tests/Feature/User/Budget/IndexBudgetTest.php) | 6 | Passed |
| User / Category | [CreateCategoryTest.php](../../backend/tests/Feature/User/Category/CreateCategoryTest.php) | 1 | Passed |
| User / Category | [DeleteCategoryTest.php](../../backend/tests/Feature/User/Category/DeleteCategoryTest.php) | 1 | Passed |
| User / Category | [IndexCategoryTest.php](../../backend/tests/Feature/User/Category/IndexCategoryTest.php) | 1 | Passed |
| User / Category | [ShowCategoryTest.php](../../backend/tests/Feature/User/Category/ShowCategoryTest.php) | 2 | Passed |
| User / Category | [UpdateCategoryTest.php](../../backend/tests/Feature/User/Category/UpdateCategoryTest.php) | 1 | Passed |
| User / Dashboard | [ShowDashboardTest.php](../../backend/tests/Feature/User/Dashboard/ShowDashboardTest.php) | 6 | Passed |
| User / Settings | [ShowSettingsTest.php](../../backend/tests/Feature/User/Settings/ShowSettingsTest.php) | 2 | Passed |
| User / Settings | [UpdatePreferencesTest.php](../../backend/tests/Feature/User/Settings/UpdatePreferencesTest.php) | 1 | Passed |
| User / Settings | [UpdateProfileTest.php](../../backend/tests/Feature/User/Settings/UpdateProfileTest.php) | 2 | Passed |
| User / Transaction | [CreateTransactionTest.php](../../backend/tests/Feature/User/Transaction/CreateTransactionTest.php) | 1 | Passed |
| User / Transaction | [IndexTransactionTest.php](../../backend/tests/Feature/User/Transaction/IndexTransactionTest.php) | 1 | Passed |
| User / Wallet | [CreateWalletTest.php](../../backend/tests/Feature/User/Wallet/CreateWalletTest.php) | 1 | Passed |
| User / Wallet | [DeleteWalletTest.php](../../backend/tests/Feature/User/Wallet/DeleteWalletTest.php) | 1 | Passed |
| User / Wallet | [IndexWalletTest.php](../../backend/tests/Feature/User/Wallet/IndexWalletTest.php) | 1 | Passed |
| User / Wallet | [UpdateWalletTest.php](../../backend/tests/Feature/User/Wallet/UpdateWalletTest.php) | 1 | Passed |
| Unit | [ExampleTest.php](../../backend/tests/Unit/ExampleTest.php) | 1 | Passed |
| **Tổng cộng** | **78 file** | **182** | **Passed** |

## 7. Phạm vi nghiệp vụ đã được xác nhận

### Authentication và Public API

- Đăng ký, đăng nhập, đăng xuất, lấy thông tin tài khoản hiện tại.
- Từ chối thông tin đăng nhập không hợp lệ và truy cập của guest vào endpoint được bảo vệ.
- Quên mật khẩu và đặt lại mật khẩu.
- Lấy cấu hình public, lấy trang đã publish và từ chối trang draft.
- Smoke test xác nhận public API có thể truy cập.

### Admin API

- Kiểm tra CRUD cho appearance, budget, category, menu, page, permission, role, transaction và user.
- Kiểm tra guest và user thường không được phép truy cập các chức năng quản trị ở những module đã có test phân quyền.
- Kiểm tra validation, phân trang, tìm kiếm, lọc, sắp xếp, dữ liệu không tồn tại và dữ liệu trùng lặp.
- Kiểm tra cấu trúc menu cha/con, kế thừa canonical và ngăn menu tự chọn chính nó làm menu cha.
- Kiểm tra gán/synchronize permission cho role và bảo vệ system role khỏi thao tác xóa.
- Kiểm tra tính toán số tiền đã chi của budget và dữ liệu tổng hợp trên admin dashboard.

### User API

- Chỉ hiển thị category, wallet, transaction, budget và dashboard thuộc phạm vi của người dùng đang đăng nhập.
- Kiểm tra CRUD category và wallet, tạo/list transaction, tạo/list budget.
- Kiểm tra budget theo kỳ, giới hạn số tiền, category dùng chung, category không hoạt động, dữ liệu trùng và tính toán chi tiêu thực tế.
- Kiểm tra dashboard chỉ trả dữ liệu tài chính của tài khoản hiện tại, category đang hoạt động và transaction đúng thứ tự thời gian.
- Kiểm tra xem/cập nhật profile, currency và preferences của người dùng.

## 8. Đánh giá chất lượng

### Điểm đã đạt

- Toàn bộ 182 test case chạy thành công, không có lỗi, thất bại hoặc test bị bỏ qua.
- Các module có logic phức tạp như Admin Budget, Admin Menu và User Budget có kiểm thử sâu cho cả luồng thành công và thất bại.
- Test suite cô lập dữ liệu bằng SQLite in-memory và `RefreshDatabase`, giúp kết quả ổn định và có thể tái lập.
- Các luồng quan trọng về authentication, authorization, ownership, validation và business rules đã có kiểm thử tự động.

### Giới hạn hiện tại

- Chưa đo code coverage vì môi trường PHP hiện không có Xdebug hoặc PCOV; tỷ lệ pass 100% không đồng nghĩa với coverage 100%.
- Một số module CRUD như Admin Category, Page, User và Transaction chủ yếu mới bao phủ happy path, ít trường hợp validation, authorization, not-found và conflict hơn Budget/Menu.
- Chưa bao gồm performance/load test, concurrency test, security scan hoặc kiểm thử tích hợp với hạ tầng production.

## 9. Kết luận

Backend **đạt yêu cầu trong phạm vi test tự động hiện có**: 182/182 test case passed với 671 assertions. Suite hiện đủ tốt để làm regression gate cho các luồng API đã được bao phủ. Tuy nhiên, cần bổ sung Unit test thực tế và code coverage trước khi dùng báo cáo này như bằng chứng về độ bao phủ toàn bộ source code.

---

### Kết quả console

```text
............................................................................
............................................................................
..............................

Tests:    182 passed (671 assertions)
Duration: 2.00s
```
