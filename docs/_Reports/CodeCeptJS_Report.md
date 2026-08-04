# BÁO CÁO KIỂM THỬ END-TO-END (CODECEPTJS)

## 1. Thông tin chung

| Hạng mục | Giá trị |
|---|---|
| Dự án | KCPM |
| Phạm vi | Giao diện web và các luồng nghiệp vụ tích hợp Frontend–Backend |
| Thư mục kiểm thử | [`codeceptjs/tests`](../../codeceptjs/tests/) |
| Framework kiểm thử | CodeceptJS 4.1.0, khai báo tại [`codeceptjs/package.json`](../../codeceptjs/package.json) |
| Trình điều khiển trình duyệt | Playwright 1.62.0 |
| Trình duyệt | Chromium, chế độ headless |
| Kích thước cửa sổ | 1440 × 900 |
| Frontend được kiểm thử | `http://localhost:3001` |
| Backend phục vụ dữ liệu | `http://localhost:8000` |
| Nhánh kiểm thử | `UnitTest` |
| Commit nền | `475ad17fbdbe2842d4d1b9080075009235e093f0` |
| Trạng thái mã nguồn | Kết quả được chạy trên working tree hiện tại, có các thay đổi CodeceptJS chưa commit so với commit nền |
| Thời điểm thực thi gần nhất | 05/08/2026 00:43, múi giờ Asia/Ho_Chi_Minh (UTC+07:00) |
| Lệnh thực thi | `cd codeceptjs && npm test` |
| Cấu hình test suite | [`codeceptjs/codecept.conf.js`](../../codeceptjs/codecept.conf.js) |
| Custom steps | [`codeceptjs/steps_file.js`](../../codeceptjs/steps_file.js) |
| Mẫu biến môi trường | [`codeceptjs/.env.example`](../../codeceptjs/.env.example) |

## 2. Mục tiêu kiểm thử

- Xác nhận các luồng chính của người dùng hoạt động đúng trên trình duyệt thật.
- Kiểm tra giao tiếp hoàn chỉnh giữa giao diện Next.js, API Laravel và cơ sở dữ liệu test/local.
- Kiểm tra xác thực, điều hướng và phân quyền giữa guest, user thường và administrator.
- Kiểm tra các thao tác tạo, xem danh sách, sửa, xóa, tìm kiếm và lọc trên các module quản trị.
- Kiểm tra các luồng tài chính của người dùng: category, wallet, transaction, budget và settings.
- Tạo một bộ regression E2E có thể chạy lại trước khi bàn giao hoặc phát hành.

## 3. Cấu hình và phương pháp

- Mỗi file `*_test.js` khai báo một `Feature`; mỗi `Scenario` được tính là một test case E2E.
- CodeceptJS điều khiển Chromium thông qua Playwright theo cấu hình tại [`codeceptjs/codecept.conf.js`](../../codeceptjs/codecept.conf.js).
- Suite tìm test theo pattern `./tests/**/*_test.js`, sử dụng URL và tài khoản từ file `.env`; cấu trúc biến cần thiết được mô tả trong [`codeceptjs/.env.example`](../../codeceptjs/.env.example).
- Lệnh `npm test` ép `HEADLESS=true`, được định nghĩa tại [`codeceptjs/package.json`](../../codeceptjs/package.json).
- Các thao tác dùng lại như đăng nhập admin/user, mở trang admin, thao tác dòng bảng và kiểm tra toast được tập trung tại [`codeceptjs/steps_file.js`](../../codeceptjs/steps_file.js).
- Plugin `retryFailedStep` được bật để thử lại step thất bại; plugin `screenshot` chụp toàn trang khi test thất bại theo [`codeceptjs/codecept.conf.js`](../../codeceptjs/codecept.conf.js).
- Kết quả trong báo cáo được lấy từ lần chạy thật toàn bộ suite, không chỉ đếm file hoặc đọc source test.

## 4. Kết quả tổng quan

| Chỉ số | Kết quả |
|---|---:|
| Tổng số file test | 60 |
| Tổng số Feature | 60 |
| Tổng số Scenario | 74 |
| Passed | 74 |
| Failed | 0 |
| Skipped | 0 |
| Tỷ lệ pass | **100%** |
| Thời gian chạy gần nhất | Khoảng 2 phút |
| Exit code | 0 |

**Kết quả chung: PASSED** — toàn bộ 74 kịch bản E2E trong phạm vi hiện tại đã chạy thành công.

## 5. Kết quả chi tiết theo module

| Nhóm chức năng | File test | Scenario | Kết quả |
|---|---:|---:|---|
| [Admin / Access control](../../codeceptjs/tests/admin/access/) | 1 | 3 | Passed |
| [Admin / Appearance](../../codeceptjs/tests/admin/appearance/) | 2 | 2 | Passed |
| [Admin / Budgets](../../codeceptjs/tests/admin/budgets/) | 4 | 5 | Passed |
| [Admin / Categories](../../codeceptjs/tests/admin/categories/) | 4 | 4 | Passed |
| [Admin / Dashboard](../../codeceptjs/tests/admin/dashboard/) | 1 | 1 | Passed |
| [Admin / Menus](../../codeceptjs/tests/admin/menus/) | 4 | 4 | Passed |
| [Admin / Pages](../../codeceptjs/tests/admin/pages/) | 4 | 4 | Passed |
| [Admin / Permissions](../../codeceptjs/tests/admin/permissions/) | 4 | 4 | Passed |
| [Admin / Roles](../../codeceptjs/tests/admin/roles/) | 4 | 4 | Passed |
| [Admin / Transactions](../../codeceptjs/tests/admin/transactions/) | 4 | 5 | Passed |
| [Admin / Users](../../codeceptjs/tests/admin/users/) | 4 | 5 | Passed |
| [Authentication](../../codeceptjs/tests/auth/) | 6 | 11 | Passed |
| [Public site](../../codeceptjs/tests/public/) | 2 | 4 | Passed |
| [Smoke test](../../codeceptjs/tests/smoke_test.js) | 1 | 1 | Passed |
| [User / Access control](../../codeceptjs/tests/user/access/) | 1 | 2 | Passed |
| [User / Budgets](../../codeceptjs/tests/user/budgets/) | 2 | 2 | Passed |
| [User / Categories](../../codeceptjs/tests/user/categories/) | 4 | 4 | Passed |
| [User / Settings](../../codeceptjs/tests/user/settings/) | 2 | 3 | Passed |
| [User / Transactions](../../codeceptjs/tests/user/transactions/) | 2 | 2 | Passed |
| [User / Wallets](../../codeceptjs/tests/user/wallets/) | 4 | 4 | Passed |
| **Tổng cộng** | **60** | **74** | **Passed** |

## 6. Chi tiết kết quả theo từng file test

Tên file trong bảng là liên kết trực tiếp tới source test. Cột `Scenario` được đối chiếu với source và tổng kết quả console của lần chạy toàn bộ suite.

| Nhóm chức năng | File test | Scenario | Kết quả |
|---|---|---:|---|
| Admin / Access control | [admin_guard_test.js](../../codeceptjs/tests/admin/access/admin_guard_test.js) | 3 | Passed |
| Admin / Appearance | [show_test.js](../../codeceptjs/tests/admin/appearance/show_test.js) | 1 | Passed |
| Admin / Appearance | [update_test.js](../../codeceptjs/tests/admin/appearance/update_test.js) | 1 | Passed |
| Admin / Budgets | [create_test.js](../../codeceptjs/tests/admin/budgets/create_test.js) | 2 | Passed |
| Admin / Budgets | [delete_test.js](../../codeceptjs/tests/admin/budgets/delete_test.js) | 1 | Passed |
| Admin / Budgets | [edit_test.js](../../codeceptjs/tests/admin/budgets/edit_test.js) | 1 | Passed |
| Admin / Budgets | [list_test.js](../../codeceptjs/tests/admin/budgets/list_test.js) | 1 | Passed |
| Admin / Categories | [create_test.js](../../codeceptjs/tests/admin/categories/create_test.js) | 1 | Passed |
| Admin / Categories | [delete_test.js](../../codeceptjs/tests/admin/categories/delete_test.js) | 1 | Passed |
| Admin / Categories | [edit_test.js](../../codeceptjs/tests/admin/categories/edit_test.js) | 1 | Passed |
| Admin / Categories | [list_test.js](../../codeceptjs/tests/admin/categories/list_test.js) | 1 | Passed |
| Admin / Dashboard | [show_test.js](../../codeceptjs/tests/admin/dashboard/show_test.js) | 1 | Passed |
| Admin / Menus | [create_test.js](../../codeceptjs/tests/admin/menus/create_test.js) | 1 | Passed |
| Admin / Menus | [delete_test.js](../../codeceptjs/tests/admin/menus/delete_test.js) | 1 | Passed |
| Admin / Menus | [edit_test.js](../../codeceptjs/tests/admin/menus/edit_test.js) | 1 | Passed |
| Admin / Menus | [list_test.js](../../codeceptjs/tests/admin/menus/list_test.js) | 1 | Passed |
| Admin / Pages | [create_test.js](../../codeceptjs/tests/admin/pages/create_test.js) | 1 | Passed |
| Admin / Pages | [delete_test.js](../../codeceptjs/tests/admin/pages/delete_test.js) | 1 | Passed |
| Admin / Pages | [edit_test.js](../../codeceptjs/tests/admin/pages/edit_test.js) | 1 | Passed |
| Admin / Pages | [list_test.js](../../codeceptjs/tests/admin/pages/list_test.js) | 1 | Passed |
| Admin / Permissions | [create_test.js](../../codeceptjs/tests/admin/permissions/create_test.js) | 1 | Passed |
| Admin / Permissions | [delete_test.js](../../codeceptjs/tests/admin/permissions/delete_test.js) | 1 | Passed |
| Admin / Permissions | [edit_test.js](../../codeceptjs/tests/admin/permissions/edit_test.js) | 1 | Passed |
| Admin / Permissions | [list_test.js](../../codeceptjs/tests/admin/permissions/list_test.js) | 1 | Passed |
| Admin / Roles | [create_test.js](../../codeceptjs/tests/admin/roles/create_test.js) | 1 | Passed |
| Admin / Roles | [delete_test.js](../../codeceptjs/tests/admin/roles/delete_test.js) | 1 | Passed |
| Admin / Roles | [edit_test.js](../../codeceptjs/tests/admin/roles/edit_test.js) | 1 | Passed |
| Admin / Roles | [list_test.js](../../codeceptjs/tests/admin/roles/list_test.js) | 1 | Passed |
| Admin / Transactions | [create_test.js](../../codeceptjs/tests/admin/transactions/create_test.js) | 2 | Passed |
| Admin / Transactions | [delete_test.js](../../codeceptjs/tests/admin/transactions/delete_test.js) | 1 | Passed |
| Admin / Transactions | [edit_test.js](../../codeceptjs/tests/admin/transactions/edit_test.js) | 1 | Passed |
| Admin / Transactions | [list_test.js](../../codeceptjs/tests/admin/transactions/list_test.js) | 1 | Passed |
| Admin / Users | [create_test.js](../../codeceptjs/tests/admin/users/create_test.js) | 2 | Passed |
| Admin / Users | [delete_test.js](../../codeceptjs/tests/admin/users/delete_test.js) | 1 | Passed |
| Admin / Users | [edit_test.js](../../codeceptjs/tests/admin/users/edit_test.js) | 1 | Passed |
| Admin / Users | [list_test.js](../../codeceptjs/tests/admin/users/list_test.js) | 1 | Passed |
| Authentication | [forgot_password_test.js](../../codeceptjs/tests/auth/forgot_password_test.js) | 2 | Passed |
| Authentication | [login_test.js](../../codeceptjs/tests/auth/login_test.js) | 2 | Passed |
| Authentication | [logout_test.js](../../codeceptjs/tests/auth/logout_test.js) | 1 | Passed |
| Authentication | [register_test.js](../../codeceptjs/tests/auth/register_test.js) | 2 | Passed |
| Authentication | [reset_password_test.js](../../codeceptjs/tests/auth/reset_password_test.js) | 2 | Passed |
| Authentication | [two_factor_challenge_test.js](../../codeceptjs/tests/auth/two_factor_challenge_test.js) | 2 | Passed |
| Public site | [home_test.js](../../codeceptjs/tests/public/home_test.js) | 2 | Passed |
| Public site | [page_test.js](../../codeceptjs/tests/public/page_test.js) | 2 | Passed |
| Smoke test | [smoke_test.js](../../codeceptjs/tests/smoke_test.js) | 1 | Passed |
| User / Access control | [user_guard_test.js](../../codeceptjs/tests/user/access/user_guard_test.js) | 2 | Passed |
| User / Budgets | [create_test.js](../../codeceptjs/tests/user/budgets/create_test.js) | 1 | Passed |
| User / Budgets | [list_test.js](../../codeceptjs/tests/user/budgets/list_test.js) | 1 | Passed |
| User / Categories | [create_test.js](../../codeceptjs/tests/user/categories/create_test.js) | 1 | Passed |
| User / Categories | [delete_test.js](../../codeceptjs/tests/user/categories/delete_test.js) | 1 | Passed |
| User / Categories | [edit_test.js](../../codeceptjs/tests/user/categories/edit_test.js) | 1 | Passed |
| User / Categories | [list_test.js](../../codeceptjs/tests/user/categories/list_test.js) | 1 | Passed |
| User / Settings | [show_test.js](../../codeceptjs/tests/user/settings/show_test.js) | 1 | Passed |
| User / Settings | [update_test.js](../../codeceptjs/tests/user/settings/update_test.js) | 2 | Passed |
| User / Transactions | [create_test.js](../../codeceptjs/tests/user/transactions/create_test.js) | 1 | Passed |
| User / Transactions | [list_test.js](../../codeceptjs/tests/user/transactions/list_test.js) | 1 | Passed |
| User / Wallets | [create_test.js](../../codeceptjs/tests/user/wallets/create_test.js) | 1 | Passed |
| User / Wallets | [delete_test.js](../../codeceptjs/tests/user/wallets/delete_test.js) | 1 | Passed |
| User / Wallets | [edit_test.js](../../codeceptjs/tests/user/wallets/edit_test.js) | 1 | Passed |
| User / Wallets | [list_test.js](../../codeceptjs/tests/user/wallets/list_test.js) | 1 | Passed |
| **Tổng cộng** | **60 file** | **74** | **Passed** |

## 7. Phạm vi nghiệp vụ đã được xác nhận

### Authentication và public site

- Kiểm tra đăng ký, đăng nhập, đăng xuất, quên mật khẩu, đặt lại mật khẩu và màn hình two-factor challenge trong [`codeceptjs/tests/auth`](../../codeceptjs/tests/auth/).
- Xác nhận landing page hiển thị đúng nội dung, liên kết đăng ký hoạt động và trang nội dung đã publish có thể truy cập trong [`codeceptjs/tests/public`](../../codeceptjs/tests/public/).
- Smoke test xác nhận frontend có thể mở và render `body` tại [`codeceptjs/tests/smoke_test.js`](../../codeceptjs/tests/smoke_test.js).

### Phân quyền truy cập

- Guest bị chuyển tới trang đăng nhập khi mở route được bảo vệ; user đã đăng nhập có thể mở các trang tài chính theo [`user_guard_test.js`](../../codeceptjs/tests/user/access/user_guard_test.js).
- Guest và user thường không thể sử dụng trang admin; administrator có thể truy cập dashboard quản trị theo [`admin_guard_test.js`](../../codeceptjs/tests/admin/access/admin_guard_test.js).
- Các guard giao diện chịu trách nhiệm điều hướng được triển khai tại [`frontend/src/components/common/auth-guard.tsx`](../../frontend/src/components/common/auth-guard.tsx) và [`frontend/src/proxy.ts`](../../frontend/src/proxy.ts).

### Chức năng administrator

- Dashboard và Appearance: kiểm tra số liệu quản trị, hiển thị/cập nhật cấu hình giao diện tại [`admin/dashboard`](../../codeceptjs/tests/admin/dashboard/) và [`admin/appearance`](../../codeceptjs/tests/admin/appearance/).
- Quản trị tài chính: kiểm tra tạo, danh sách, sửa và xóa budget, category, transaction tại [`admin/budgets`](../../codeceptjs/tests/admin/budgets/), [`admin/categories`](../../codeceptjs/tests/admin/categories/) và [`admin/transactions`](../../codeceptjs/tests/admin/transactions/).
- Quản trị nội dung: kiểm tra CRUD menu và page, tìm kiếm, lọc trạng thái và hiển thị trang đã publish tại [`admin/menus`](../../codeceptjs/tests/admin/menus/) và [`admin/pages`](../../codeceptjs/tests/admin/pages/).
- Quản trị truy cập: kiểm tra CRUD user, role và permission tại [`admin/users`](../../codeceptjs/tests/admin/users/), [`admin/roles`](../../codeceptjs/tests/admin/roles/) và [`admin/permissions`](../../codeceptjs/tests/admin/permissions/).

### Chức năng người dùng

- Category: xem danh sách riêng, validation, tạo, sửa và xóa trong [`user/categories`](../../codeceptjs/tests/user/categories/).
- Wallet: xem ví và lịch sử giao dịch, validation, tạo, sửa và xóa trong [`user/wallets`](../../codeceptjs/tests/user/wallets/).
- Transaction: xem danh sách, bộ lọc loại, validation và tạo giao dịch chi tiêu trong [`user/transactions`](../../codeceptjs/tests/user/transactions/).
- Budget: xem tổng quan tháng và tạo ngân sách cho category riêng trong [`user/budgets`](../../codeceptjs/tests/user/budgets/).
- Settings: xem biểu mẫu, cập nhật hồ sơ và đơn vị tiền tệ trong [`user/settings`](../../codeceptjs/tests/user/settings/).

## 8. Đối chiếu với mã nguồn ứng dụng

| Phạm vi | Mã nguồn giao diện chính | API/route backend chính |
|---|---|---|
| Authentication | [`frontend/src/app/(auth)`](<../../frontend/src/app/(auth)/>) | [`backend/app/Http/Controllers/Api/V1/AuthController.php`](../../backend/app/Http/Controllers/Api/V1/AuthController.php) |
| Public site | [`frontend/src/app/page.tsx`](../../frontend/src/app/page.tsx), [`frontend/src/app/p/[slug]/page.tsx`](<../../frontend/src/app/p/[slug]/page.tsx>) | [`backend/app/Http/Controllers/Api/V1/PublicSiteController.php`](../../backend/app/Http/Controllers/Api/V1/PublicSiteController.php) |
| Admin dashboard | [`frontend/src/features/admin/dashboard/admin-dashboard-view.tsx`](../../frontend/src/features/admin/dashboard/admin-dashboard-view.tsx) | [`backend/app/Http/Controllers/Api/V1/Admin/DashboardController.php`](../../backend/app/Http/Controllers/Api/V1/Admin/DashboardController.php) |
| Admin finance | [`frontend/src/features/admin/finance`](../../frontend/src/features/admin/finance/) | [`backend/app/Http/Controllers/Api/V1/Admin`](../../backend/app/Http/Controllers/Api/V1/Admin/) |
| Admin content | [`frontend/src/features/admin/content`](../../frontend/src/features/admin/content/) | [`backend/app/Http/Controllers/Api/V1/Admin`](../../backend/app/Http/Controllers/Api/V1/Admin/) |
| Admin access control | [`frontend/src/features/admin/access-control`](../../frontend/src/features/admin/access-control/) | [`backend/app/Http/Controllers/Api/V1/Admin`](../../backend/app/Http/Controllers/Api/V1/Admin/) |
| User finance | [`frontend/src/features`](../../frontend/src/features/) | [`backend/app/Http/Controllers/Api/V1/User`](../../backend/app/Http/Controllers/Api/V1/User/) |
| Toàn bộ API route | — | [`backend/routes/api.php`](../../backend/routes/api.php) |

## 9. Đánh giá chất lượng

### Điểm đã đạt

- 74/74 scenario chạy thành công trên trình duyệt Chromium; không có test thất bại hoặc bị bỏ qua.
- Đã bao phủ các luồng quan trọng từ giao diện đến API: authentication, authorization, CRUD quản trị và nghiệp vụ tài chính người dùng.
- Nhiều luồng tạo dữ liệu sử dụng tên/email có timestamp để giảm xung đột giữa các lần chạy.
- Các test xóa và một số test public page tự tạo dữ liệu mục tiêu rồi dọn dữ liệu ngay trong kịch bản.
- Khi xảy ra lỗi, cấu hình hiện tại hỗ trợ tự động chụp ảnh toàn trang để phục vụ điều tra.

### Giới hạn hiện tại

- Mới chạy trên Chromium ở viewport desktop 1440 × 900; chưa xác nhận Firefox, WebKit, tablet hoặc mobile.
- Kết quả phản ánh môi trường local đang chạy tại thời điểm kiểm thử, chưa phải môi trường staging/production.
- Suite E2E có thể ghi dữ liệu vào database local; không phải mọi kịch bản tạo dữ liệu đều có bước cleanup tương ứng.
- `retryFailedStep` có thể làm một step không ổn định vẫn vượt qua ở lần thử lại; nên theo dõi flakiness riêng trong CI.
- Chưa kiểm tra accessibility, visual regression, performance/load, concurrency hoặc security chuyên sâu.
- Tỷ lệ pass 100% không đồng nghĩa với code coverage 100%; CodeceptJS report này đánh giá theo luồng người dùng, không đo số dòng source đã thực thi.

## 10. Khuyến nghị

1. Chạy suite trong CI với database test riêng và reset dữ liệu trước mỗi lần chạy.
2. Lưu console log, screenshot/video và báo cáo HTML/JUnit thành CI artifacts để dễ truy vết lịch sử.
3. Bổ sung matrix Firefox và WebKit cho các luồng authentication, CRUD và thanh toán/tài chính quan trọng.
4. Bổ sung viewport mobile cho landing page, form đăng nhập và các màn hình finance.
5. Thêm cleanup cho các scenario tạo dữ liệu nhưng không xóa, hoặc xây dựng API/helper chuyên chuẩn bị và dọn fixture.
6. Theo dõi số lần retry và chạy lặp các test hay dao động để phát hiện flaky test.

## 11. Kết luận

Ứng dụng **đạt yêu cầu trong phạm vi E2E hiện có**: 74/74 scenario đã passed trên Chromium headless với exit code 0. Bộ test đã tạo được regression gate tốt cho các luồng chính của guest, user và admin. Trước khi xem đây là bằng chứng sẵn sàng production, nên bổ sung chạy đa trình duyệt, môi trường CI cô lập, quản lý test data và các lớp kiểm thử accessibility/performance/security.

---

### Kết quả console

```text
OK  | 74 passed   // 2m
```
