# BÁO CÁO UNIT TEST CASE — TUẦN 4

## 1. Thông tin chung

| Hạng mục | Giá trị |
|---|---|
| Dự án | KCPM |
| Tuần báo cáo | Tuần 4 |
| Phạm vi | Backend Laravel API |
| Thư mục kiểm thử | [`backend/tests`](../../backend/tests/) |
| Framework kiểm thử | Pest 4.4.1 / PHPUnit 12 — khai báo tại [`backend/composer.json`](../../backend/composer.json) |
| Framework ứng dụng | Laravel 12.53.0 |
| Phiên bản PHP | PHP 8.4.7 |
| Nhánh kiểm thử | `UnitTest` |
| Commit nền của lần chạy | `271da695e1373551e763b50d0a61d133e9a006aa` |
| Trạng thái source test | Bao gồm các test case tuần 4 đang có trong working tree tại thời điểm chạy |
| Thời điểm thực thi gần nhất | 11/08/2026 23:20:59, múi giờ Asia/Ho_Chi_Minh (UTC+07:00) |
| Lệnh thực thi | `php artisan test --compact --log-junit /tmp/cashback-week4-junit.xml` |
| Cấu hình test suite | [`backend/phpunit.xml`](../../backend/phpunit.xml) |
| Cấu hình Pest và test helper | [`backend/tests/Pest.php`](../../backend/tests/Pest.php) |

## 2. Nội dung thực hiện trong tuần 4

Trong tuần 4, các thành viên trong nhóm tiếp tục mở rộng bộ test case backend dựa trên kết quả của báo cáo trước. Trọng tâm là bổ sung các trường hợp phân quyền, validation, dữ liệu không tồn tại, tìm kiếm/lọc/sắp xếp/phân trang, ràng buộc sở hữu dữ liệu, quy tắc nghiệp vụ và bảo mật phản hồi API.

Các phần được mở rộng nhiều nhất gồm:

- Authentication, bao gồm đăng ký, đăng nhập, quên/đặt lại mật khẩu, rate limit và xác thực hai bước.
- CRUD phía Admin cho category, page, permission, role, transaction và user.
- CRUD phía User cho category, transaction, wallet, settings và dashboard.
- Public API, appearance và dashboard tổng hợp.
- Các quy tắc dữ liệu liên quan đến budget, menu cha/con, role/permission, wallet mặc định, transaction đã ghi nhận và quyền sở hữu tài nguyên.

## 3. Mục tiêu kiểm thử

- Xác nhận các API backend hoạt động đúng với luồng nghiệp vụ đã triển khai.
- Kiểm tra đầy đủ quyền truy cập của guest, user thường và admin.
- Kiểm tra các luồng thành công, validation, not-found, conflict và dữ liệu trùng lặp.
- Bảo đảm người dùng không truy cập hoặc thao tác dữ liệu thuộc tài khoản khác.
- Kiểm tra response không làm lộ mật khẩu, secret xác thực hoặc dữ liệu nội bộ.
- Tạo regression suite có thể chạy lại trên môi trường test độc lập.

## 4. Cấu hình và phương pháp

- Test suite sử dụng Pest kết hợp Laravel HTTP testing.
- Feature test dùng `RefreshDatabase` theo [`backend/tests/Pest.php`](../../backend/tests/Pest.php), bảo đảm dữ liệu được làm mới giữa các test.
- Database kiểm thử là SQLite in-memory: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`.
- Cache dùng `array`, queue dùng `sync`, session và mail dùng `array` để tránh phụ thuộc hạ tầng ngoài.
- Dữ liệu test được khởi tạo bằng model factory và helper tài khoản `adminUser()` / `regularUser()`.
- Số liệu trong báo cáo được lấy từ lần chạy thật của toàn bộ Pest/PHPUnit suite và JUnit metadata.
- Các file JavaScript nằm ngoài PHPUnit suite không được tính vào số liệu báo cáo này.

## 5. Kết quả tổng quan

| Chỉ số | Kết quả |
|---|---:|
| Tổng số file test PHP | 79 |
| Unit test case | 1 |
| Feature/API test case | 438 |
| Tổng test case được khai báo | 439 |
| Test case đã thực thi và pass | 434 |
| Todo/Skipped | 5 |
| Failed | 0 |
| Errors | 0 |
| Tổng assertion | 1.397 |
| Tỷ lệ pass trên số test đã thực thi | **100%** |
| Tỷ lệ hoàn thành trên tổng test được khai báo | **98,86%** |
| Thời gian chạy | 5,44 giây |
| Exit code | 0 |

**Kết quả chung:** toàn bộ 434 test case đã thực thi đều **PASSED**. Bộ test chưa hoàn tất tuyệt đối vì còn 5 test case được đánh dấu `todo`.

## 6. So sánh với báo cáo trước

| Chỉ số | Báo cáo trước | Tuần 4 | Thay đổi |
|---|---:|---:|---:|
| File test PHP | 78 | 79 | **+1** |
| Tổng test case khai báo | 182 | 439 | **+257** |
| Test case pass | 182 | 434 | **+252** |
| Todo/Skipped | 0 | 5 | **+5** |
| Assertion | 671 | 1.397 | **+726** |
| Failed | 0 | 0 | 0 |
| Errors | 0 | 0 | 0 |

### Mức tăng test case theo module

| Nhóm chức năng | Báo cáo trước | Tuần 4 | Bổ sung |
|---|---:|---:|---:|
| Authentication | 8 | 30 | **+22** |
| Public API | 3 | 10 | **+7** |
| Admin / Appearance | 2 | 11 | **+9** |
| Admin / Budget | 37 | 39 | **+2** |
| Admin / Category | 5 | 29 | **+24** |
| Admin / Dashboard | 1 | 4 | **+3** |
| Admin / Menu | 35 | 37 | **+2** |
| Admin / Page | 5 | 27 | **+22** |
| Admin / Permission | 9 | 27 | **+18** |
| Admin / Role | 8 | 29 | **+21** |
| Admin / Transaction | 6 | 33 | **+27** |
| Admin / User | 5 | 32 | **+27** |
| User / Category | 6 | 29 | **+23** |
| User / Dashboard | 6 | 9 | **+3** |
| User / Settings | 5 | 18 | **+13** |
| User / Transaction | 2 | 17 | **+15** |
| User / Wallet | 4 | 23 | **+19** |
| Các module không đổi | 20 | 20 | 0 |
| **Tổng cộng** | **182** | **439** | **+257** |

## 7. Kết quả chi tiết theo module

| Nhóm chức năng | File test | Test case | Passed | Todo | Kết quả |
|---|---:|---:|---:|---:|---|
| [Unit](../../backend/tests/Unit/) | 1 | 1 | 1 | 0 | Passed |
| [Smoke test](../../backend/tests/Feature/SmokeTest.php) | 1 | 1 | 1 | 0 | Passed |
| [Authentication](../../backend/tests/Feature/Auth/) | 7 | 30 | 30 | 0 | Passed |
| [Public API](../../backend/tests/Feature/Public/) | 2 | 10 | 10 | 0 | Passed |
| [Admin / Appearance](../../backend/tests/Feature/Admin/Appearance/) | 2 | 11 | 11 | 0 | Passed |
| [Admin / Budget](../../backend/tests/Feature/Admin/Budget/) | 6 | 39 | 38 | 1 | Passed + Todo |
| [Admin / Category](../../backend/tests/Feature/Admin/Category/) | 5 | 29 | 29 | 0 | Passed |
| [Admin / Dashboard](../../backend/tests/Feature/Admin/Dashboard/) | 1 | 4 | 4 | 0 | Passed |
| [Admin / Menu](../../backend/tests/Feature/Admin/Menu/) | 6 | 37 | 35 | 2 | Passed + Todo |
| [Admin / Page](../../backend/tests/Feature/Admin/Page/) | 5 | 27 | 27 | 0 | Passed |
| [Admin / Permission](../../backend/tests/Feature/Admin/Permission/) | 6 | 27 | 27 | 0 | Passed |
| [Admin / Role](../../backend/tests/Feature/Admin/Role/) | 6 | 29 | 29 | 0 | Passed |
| [Admin / Transaction](../../backend/tests/Feature/Admin/Transaction/) | 6 | 33 | 32 | 1 | Passed + Todo |
| [Admin / User](../../backend/tests/Feature/Admin/User/) | 5 | 32 | 31 | 1 | Passed + Todo |
| [API V1 / Admin / PermissionController](../../backend/tests/Feature/Api/V1/Admin/PermissionControllerTest.php) | 1 | 6 | 6 | 0 | Passed |
| [API V1 / Admin / RoleController](../../backend/tests/Feature/Api/V1/Admin/RoleControllerTest.php) | 1 | 6 | 6 | 0 | Passed |
| [API V1 / User / SettingsController](../../backend/tests/Feature/Api/V1/User/SettingsControllerTest.php) | 1 | 3 | 3 | 0 | Passed |
| [User / Budget](../../backend/tests/Feature/User/Budget/) | 2 | 18 | 18 | 0 | Passed |
| [User / Category](../../backend/tests/Feature/User/Category/) | 5 | 29 | 29 | 0 | Passed |
| [User / Dashboard](../../backend/tests/Feature/User/Dashboard/) | 1 | 9 | 9 | 0 | Passed |
| [User / Settings](../../backend/tests/Feature/User/Settings/) | 3 | 18 | 18 | 0 | Passed |
| [User / Transaction](../../backend/tests/Feature/User/Transaction/) | 2 | 17 | 17 | 0 | Passed |
| [User / Wallet](../../backend/tests/Feature/User/Wallet/) | 4 | 23 | 23 | 0 | Passed |
| **Tổng cộng** | **79** | **439** | **434** | **5** | **Passed + Todo** |

## 8. Chi tiết các chức năng và test case

### 8.1. Authentication — 30 test case

- **Đăng ký:** đăng ký thành công; bắt buộc nhập đủ trường; từ chối email đã tồn tại; từ chối xác nhận mật khẩu không khớp; kiểm tra sự kiện `Registered` được phát.
- **Đăng nhập:** đăng nhập thành công; từ chối mật khẩu sai và email không tồn tại; kiểm tra trường bắt buộc, định dạng email/remember; không trả secret xác thực; giới hạn tần suất sau nhiều lần đăng nhập sai.
- **Đăng xuất và thông tin tài khoản:** user đã đăng nhập có thể logout/lấy thông tin tài khoản; guest bị từ chối.
- **Quên mật khẩu:** gửi thông báo reset; validation email; từ chối email không tồn tại; throttle yêu cầu lặp lại.
- **Đặt lại mật khẩu:** cập nhật mật khẩu thành công; từ chối token sai; kiểm tra xác nhận mật khẩu; token không tái sử dụng; phát sự kiện `PasswordReset`.
- **Xác thực hai bước:** chấp nhận mã OTP hợp lệ; từ chối mã sai; dùng recovery code một lần; từ chối phiên 2FA hết hạn; bắt buộc có OTP hoặc recovery code.

### 8.2. Public API và Smoke test — 11 test case

- Trả danh sách menu đang hoạt động và đúng thứ tự hiển thị.
- Dùng locale mặc định khi không truyền locale; từ chối locale không hỗ trợ.
- Trả appearance đã localize và URL asset đúng cấu hình.
- Lấy trang đã publish; từ chối trang draft; trả 404 cho slug không tồn tại; hỗ trợ nested slug.
- Không làm lộ tác giả hoặc category nội bộ trong public page response.
- Smoke test xác nhận public API có thể truy cập.

### 8.3. Admin / Appearance — 11 test case

- Admin xem và cập nhật cấu hình giao diện; guest và user thường bị từ chối.
- Response trả đúng danh sách ngôn ngữ và logo rỗng mặc định.
- Validation độ dài nội dung dịch và loại file upload.
- Upload logo và lưu đường dẫn; cập nhật cấu hình chung không làm mất logo hiện có.

### 8.4. Admin / Budget — 39 test case

- **Create:** phân quyền; tạo budget và lưu `spent`; chuẩn hóa note rỗng; validation trường bắt buộc, khóa ngoại, giới hạn số tiền, period/status; ngăn trùng user-category-period; chỉ chấp nhận category của user hoặc category dùng chung.
- **Index:** phân quyền; phân trang mặc định; tìm kiếm theo user, email, category, note; lọc period/status/user/category; tính chi tiêu theo kỳ hiện tại và chỉ tính expense đã posted; sắp xếp, phân trang và validation query.
- **Options/Show:** trả user và category đang hoạt động; trả chi tiết user/category và số tiền đã chi; xử lý 404.
- **Update/Delete:** cập nhật, ngăn tổ hợp trùng, cho phép giữ tổ hợp hiện tại, validation, xóa thành công và xử lý 404.
- Còn 1 case `todo`: tách riêng kiểm tra tìm budget theo ID.

### 8.5. Admin / Category — 29 test case

- Admin thực hiện đầy đủ create, list, show, update và delete; guest/user thường bị từ chối ở từng endpoint.
- Validation trường bắt buộc, màu sắc, trạng thái và tên global trùng lặp.
- Cho phép category global trùng tên với category private nhưng không trùng category global khác.
- Danh sách chỉ chứa category global; hỗ trợ search, filter, sort, pagination và validation query.
- Không cho admin xem category private của user qua admin endpoint.
- Khi xóa category global: budget liên quan bị xóa, transaction được gỡ liên kết category.
- Xử lý 404 cho show/update/delete với ID không tồn tại.

### 8.6. Admin / Dashboard — 4 test case

- Admin xem được số liệu tổng hợp dashboard; guest và user thường bị từ chối.
- Kiểm tra từng metric chỉ tổng hợp đúng loại transaction, trạng thái và phạm vi dữ liệu liên quan.

### 8.7. Admin / Menu — 37 test case

- **Create:** phân quyền; tạo menu gốc/con; menu con kế thừa canonical; validation trường bắt buộc, canonical, target, status và `parent_id`.
- **Index:** tìm theo title/URL; lọc status/canonical/parent; sắp xếp, phân trang và validation query.
- **Parent options:** chỉ trả menu gốc theo thứ tự title; loại menu hiện tại; kiểm tra ID loại trừ tồn tại.
- **Show/Update:** trả thông tin menu cha; ngăn chọn chính nó làm cha; kế thừa canonical khi đổi cha; validation và 404.
- **Delete:** xóa menu; khi xóa menu cha thì `parent_id` của menu con được đưa về null; xử lý 404.
- Còn 2 case `todo`: chặn URL `javascript:` và chặn chu trình cha/con gián tiếp.

### 8.8. Admin / Page — 27 test case

- Admin thực hiện đầy đủ create, list, show, update và delete; guest/user thường bị từ chối.
- Validation title, status, category và slug.
- Tự sinh slug duy nhất mà không thay đổi title; từ chối slug truyền vào bị trùng.
- Khi cập nhật, page được giữ slug của chính nó nhưng không được dùng slug của page khác.
- Hỗ trợ search, filter, sort, pagination, validation query và xử lý 404.

### 8.9. Admin / Permission — 27 test case

- Admin thực hiện create, list, options, show, update và delete; guest/user thường bị từ chối.
- Validation tên bắt buộc và duy nhất khi tạo/cập nhật.
- Hỗ trợ search, sort, pagination và validation query.
- Khi xóa permission, permission được detach khỏi các role liên quan.
- Xử lý 404 cho show/update/delete.

### 8.10. Admin / Role — 29 test case

- Admin thực hiện create, list, options, show, update và delete; guest/user thường bị từ chối.
- Tạo role kèm permission; validation tên, permission và tên trùng trong guard `web`.
- Bảo vệ system role `admin` và `super-admin` khỏi thao tác xóa.
- Hỗ trợ search, sort, pagination và validation query.
- Đồng bộ permission khi cập nhật; bỏ trường permission thì giữ nguyên; mảng rỗng thì gỡ toàn bộ permission.
- Xử lý 404 cho show/update/delete.

### 8.11. Admin / Transaction — 33 test case

- **Create:** phân quyền; validation trường bắt buộc và enum/giá trị; wallet phải thuộc user được chọn; không dùng category private của user khác; chuẩn hóa trường tùy chọn.
- **Index:** search theo dữ liệu liên quan; filter theo trường nghiệp vụ và khoảng ngày bao gồm hai đầu; sort, pagination và validation query.
- **Options/Show:** trả option cần thiết, xem chi tiết và xử lý 404.
- **Update/Delete:** phân quyền; validation; kiểm tra quyền sở hữu wallet theo user được chọn; cập nhật/xóa và xử lý 404.
- Còn 1 case `todo`: tách riêng kiểm tra tìm transaction theo ID.

### 8.12. Admin / User — 32 test case

- **Create:** phân quyền; validation trường bắt buộc, email trùng, role ID sai/trùng; tạo user với role và mật khẩu được hash.
- **Index:** search theo tên/email; lọc theo role và ngày tạo; sort, pagination và validation query.
- **Show/Update:** phân quyền; validation trường bắt buộc/duy nhất; giữ mật khẩu khi không truyền; cập nhật mật khẩu/role; mảng role rỗng gỡ toàn bộ role; xử lý 404.
- **Delete:** xóa user cùng dữ liệu tài chính và preferences; xử lý 404.
- Còn 1 case `todo`: tách riêng kiểm tra tìm user theo ID.

### 8.13. API V1 controller regression — 15 test case

- PermissionController: list, options, create, show, update và delete.
- RoleController: list, options, create, show, update và delete.
- SettingsController: xem settings, cập nhật profile và cập nhật preferences.

### 8.14. User / Budget — 18 test case

- User tạo budget cho chính mình; budget luôn khởi tạo active; note rỗng được lưu null.
- Mỗi category chỉ có một budget cho từng period; cho phép cùng category ở period khác.
- Validation trường bắt buộc, giới hạn số tiền, period, category inactive, category của user khác; chấp nhận category dùng chung.
- Danh sách chỉ trả budget active của tài khoản hiện tại; admin cũng có thể xem budget cá nhân.
- Tính chi tiêu posted theo đúng user và kỳ tháng/năm; sắp xếp theo period rồi ID mới nhất.

### 8.15. User / Category — 29 test case

- User thực hiện create, list, show, update và delete category private; guest bị từ chối.
- Validation name/color; category private luôn active khi tạo.
- Ngăn trùng tên với category đang nhìn thấy; cho phép hai user khác nhau dùng cùng tên private.
- Chỉ trả category global và private của user, bỏ category inactive, ưu tiên global trước private.
- Không xem/sửa/xóa category private của user khác; không sửa/xóa category global.
- Không xóa category đang được transaction hoặc budget sử dụng; xử lý 404.

### 8.16. User / Dashboard — 9 test case

- Guest bị từ chối; user/admin xem dashboard tài chính của chính mình.
- Kiểm tra cấu trúc response, chỉ trả dữ liệu của tài khoản hiện tại và category active.
- Không làm lộ category private của user khác.
- Số dư wallet bỏ qua transaction pending.
- User mới nhận collection tài chính rỗng; transaction được sắp xếp theo ngày giao dịch mới nhất.

### 8.17. User / Settings — 18 test case

- Xem profile/preferences; mặc định dùng VND khi chưa có preference; guest và admin bị chặn tại phạm vi user-only.
- Response không chứa trường nhạy cảm.
- Cập nhật currency; validation currency hỗ trợ/bắt buộc; preference cũ được thay thế.
- Cập nhật profile; validation name/email; từ chối email đã tồn tại.
- Đổi email sẽ xóa `email_verified_at`; giữ email sẽ bảo toàn trạng thái xác minh.

### 8.18. User / Transaction — 17 test case

- Tạo transaction; guest bị từ chối; không dùng wallet/category private của user khác hoặc category inactive.
- Validation trường bắt buộc, amount, type, date và độ dài label; chuẩn hóa category/note/labels rỗng.
- Danh sách chỉ chứa transaction của user hiện tại.
- Search theo note; lọc type/status/wallet/category/khoảng ngày; sort và pagination.
- Validation query, từ chối `end_date` trước `start_date`, không cho lọc theo wallet của user khác.

### 8.19. User / Wallet — 23 test case

- Tạo, xem danh sách, cập nhật và xóa wallet; guest bị từ chối và user không thao tác wallet của người khác.
- Validation trường bắt buộc, số tiền và tên trùng trong cùng tài khoản; cho phép user khác nhau dùng cùng tên wallet.
- Khi tạo/cập nhật wallet mặc định mới, wallet mặc định cũ được bỏ cờ.
- Wallet duy nhất vẫn là mặc định nếu cố cập nhật thành không mặc định.
- Danh sách ưu tiên wallet mặc định rồi sắp xếp theo tên.
- Số dư chỉ tính income/expense đã posted.
- Xóa wallet mặc định sẽ chọn wallet cũ nhất còn lại; transaction vẫn được giữ; xử lý 404.

### 8.20. Unit test cơ bản — 1 test case

- `ExampleTest.php` xác nhận biểu thức `true` là đúng. Đây là test khung, chưa đại diện cho logic đơn vị thực tế của ứng dụng.

## 9. Danh sách test case đang Todo

| STT | Module | Test case | File |
|---:|---|---|---|
| 1 | Admin / Budget | Tìm budget theo ID | [`IndexBudgetTest.php`](../../backend/tests/Feature/Admin/Budget/IndexBudgetTest.php) |
| 2 | Admin / Menu | Từ chối URL thực thi `javascript:` khi tạo menu | [`CreateMenuTest.php`](../../backend/tests/Feature/Admin/Menu/CreateMenuTest.php) |
| 3 | Admin / Menu | Không cho tạo chu trình cha/con gián tiếp khi cập nhật menu | [`UpdateMenuTest.php`](../../backend/tests/Feature/Admin/Menu/UpdateMenuTest.php) |
| 4 | Admin / Transaction | Tìm transaction theo ID | [`IndexTransactionTest.php`](../../backend/tests/Feature/Admin/Transaction/IndexTransactionTest.php) |
| 5 | Admin / User | Tìm user theo ID | [`IndexUserTest.php`](../../backend/tests/Feature/Admin/User/IndexUserTest.php) |

## 10. Kết quả chi tiết theo từng file test

| Nhóm chức năng | File test | Test case | Passed | Todo |
|---|---|---:|---:|---:|
| Unit | [ExampleTest.php](../../backend/tests/Unit/ExampleTest.php) | 1 | 1 | 0 |
| Admin / Appearance | [ShowAppearanceTest.php](../../backend/tests/Feature/Admin/Appearance/ShowAppearanceTest.php) | 4 | 4 | 0 |
| Admin / Appearance | [UpdateAppearanceTest.php](../../backend/tests/Feature/Admin/Appearance/UpdateAppearanceTest.php) | 7 | 7 | 0 |
| Admin / Budget | [CreateBudgetTest.php](../../backend/tests/Feature/Admin/Budget/CreateBudgetTest.php) | 10 | 10 | 0 |
| Admin / Budget | [DeleteBudgetTest.php](../../backend/tests/Feature/Admin/Budget/DeleteBudgetTest.php) | 4 | 4 | 0 |
| Admin / Budget | [IndexBudgetTest.php](../../backend/tests/Feature/Admin/Budget/IndexBudgetTest.php) | 10 | 9 | 1 |
| Admin / Budget | [OptionsBudgetTest.php](../../backend/tests/Feature/Admin/Budget/OptionsBudgetTest.php) | 3 | 3 | 0 |
| Admin / Budget | [ShowBudgetTest.php](../../backend/tests/Feature/Admin/Budget/ShowBudgetTest.php) | 5 | 5 | 0 |
| Admin / Budget | [UpdateBudgetTest.php](../../backend/tests/Feature/Admin/Budget/UpdateBudgetTest.php) | 7 | 7 | 0 |
| Admin / Category | [CreateCategoryTest.php](../../backend/tests/Feature/Admin/Category/CreateCategoryTest.php) | 7 | 7 | 0 |
| Admin / Category | [DeleteCategoryTest.php](../../backend/tests/Feature/Admin/Category/DeleteCategoryTest.php) | 5 | 5 | 0 |
| Admin / Category | [IndexCategoryTest.php](../../backend/tests/Feature/Admin/Category/IndexCategoryTest.php) | 6 | 6 | 0 |
| Admin / Category | [ShowCategoryTest.php](../../backend/tests/Feature/Admin/Category/ShowCategoryTest.php) | 5 | 5 | 0 |
| Admin / Category | [UpdateCategoryTest.php](../../backend/tests/Feature/Admin/Category/UpdateCategoryTest.php) | 6 | 6 | 0 |
| Admin / Dashboard | [ShowDashboardTest.php](../../backend/tests/Feature/Admin/Dashboard/ShowDashboardTest.php) | 4 | 4 | 0 |
| Admin / Menu | [CreateMenuTest.php](../../backend/tests/Feature/Admin/Menu/CreateMenuTest.php) | 8 | 7 | 1 |
| Admin / Menu | [DeleteMenuTest.php](../../backend/tests/Feature/Admin/Menu/DeleteMenuTest.php) | 5 | 5 | 0 |
| Admin / Menu | [IndexMenuTest.php](../../backend/tests/Feature/Admin/Menu/IndexMenuTest.php) | 7 | 7 | 0 |
| Admin / Menu | [ParentOptionsMenuTest.php](../../backend/tests/Feature/Admin/Menu/ParentOptionsMenuTest.php) | 5 | 5 | 0 |
| Admin / Menu | [ShowMenuTest.php](../../backend/tests/Feature/Admin/Menu/ShowMenuTest.php) | 5 | 5 | 0 |
| Admin / Menu | [UpdateMenuTest.php](../../backend/tests/Feature/Admin/Menu/UpdateMenuTest.php) | 7 | 6 | 1 |
| Admin / Page | [CreatePageTest.php](../../backend/tests/Feature/Admin/Page/CreatePageTest.php) | 7 | 7 | 0 |
| Admin / Page | [DeletePageTest.php](../../backend/tests/Feature/Admin/Page/DeletePageTest.php) | 4 | 4 | 0 |
| Admin / Page | [IndexPageTest.php](../../backend/tests/Feature/Admin/Page/IndexPageTest.php) | 5 | 5 | 0 |
| Admin / Page | [ShowPageTest.php](../../backend/tests/Feature/Admin/Page/ShowPageTest.php) | 4 | 4 | 0 |
| Admin / Page | [UpdatePageTest.php](../../backend/tests/Feature/Admin/Page/UpdatePageTest.php) | 7 | 7 | 0 |
| Admin / Permission | [CreatePermissionTest.php](../../backend/tests/Feature/Admin/Permission/CreatePermissionTest.php) | 5 | 5 | 0 |
| Admin / Permission | [DeletePermissionTest.php](../../backend/tests/Feature/Admin/Permission/DeletePermissionTest.php) | 5 | 5 | 0 |
| Admin / Permission | [IndexPermissionTest.php](../../backend/tests/Feature/Admin/Permission/IndexPermissionTest.php) | 5 | 5 | 0 |
| Admin / Permission | [OptionsPermissionTest.php](../../backend/tests/Feature/Admin/Permission/OptionsPermissionTest.php) | 3 | 3 | 0 |
| Admin / Permission | [ShowPermissionTest.php](../../backend/tests/Feature/Admin/Permission/ShowPermissionTest.php) | 4 | 4 | 0 |
| Admin / Permission | [UpdatePermissionTest.php](../../backend/tests/Feature/Admin/Permission/UpdatePermissionTest.php) | 5 | 5 | 0 |
| Admin / Role | [CreateRoleTest.php](../../backend/tests/Feature/Admin/Role/CreateRoleTest.php) | 5 | 5 | 0 |
| Admin / Role | [DeleteRoleTest.php](../../backend/tests/Feature/Admin/Role/DeleteRoleTest.php) | 6 | 6 | 0 |
| Admin / Role | [IndexRoleTest.php](../../backend/tests/Feature/Admin/Role/IndexRoleTest.php) | 5 | 5 | 0 |
| Admin / Role | [OptionsRoleTest.php](../../backend/tests/Feature/Admin/Role/OptionsRoleTest.php) | 3 | 3 | 0 |
| Admin / Role | [ShowRoleTest.php](../../backend/tests/Feature/Admin/Role/ShowRoleTest.php) | 4 | 4 | 0 |
| Admin / Role | [UpdateRoleTest.php](../../backend/tests/Feature/Admin/Role/UpdateRoleTest.php) | 6 | 6 | 0 |
| Admin / Transaction | [CreateTransactionTest.php](../../backend/tests/Feature/Admin/Transaction/CreateTransactionTest.php) | 8 | 8 | 0 |
| Admin / Transaction | [DeleteTransactionTest.php](../../backend/tests/Feature/Admin/Transaction/DeleteTransactionTest.php) | 4 | 4 | 0 |
| Admin / Transaction | [IndexTransactionTest.php](../../backend/tests/Feature/Admin/Transaction/IndexTransactionTest.php) | 8 | 7 | 1 |
| Admin / Transaction | [OptionsTransactionTest.php](../../backend/tests/Feature/Admin/Transaction/OptionsTransactionTest.php) | 3 | 3 | 0 |
| Admin / Transaction | [ShowTransactionTest.php](../../backend/tests/Feature/Admin/Transaction/ShowTransactionTest.php) | 4 | 4 | 0 |
| Admin / Transaction | [UpdateTransactionTest.php](../../backend/tests/Feature/Admin/Transaction/UpdateTransactionTest.php) | 6 | 6 | 0 |
| Admin / User | [CreateUserTest.php](../../backend/tests/Feature/Admin/User/CreateUserTest.php) | 7 | 7 | 0 |
| Admin / User | [DeleteUserTest.php](../../backend/tests/Feature/Admin/User/DeleteUserTest.php) | 5 | 5 | 0 |
| Admin / User | [IndexUserTest.php](../../backend/tests/Feature/Admin/User/IndexUserTest.php) | 8 | 7 | 1 |
| Admin / User | [ShowUserTest.php](../../backend/tests/Feature/Admin/User/ShowUserTest.php) | 4 | 4 | 0 |
| Admin / User | [UpdateUserTest.php](../../backend/tests/Feature/Admin/User/UpdateUserTest.php) | 8 | 8 | 0 |
| API V1 / Admin | [PermissionControllerTest.php](../../backend/tests/Feature/Api/V1/Admin/PermissionControllerTest.php) | 6 | 6 | 0 |
| API V1 / Admin | [RoleControllerTest.php](../../backend/tests/Feature/Api/V1/Admin/RoleControllerTest.php) | 6 | 6 | 0 |
| API V1 / User | [SettingsControllerTest.php](../../backend/tests/Feature/Api/V1/User/SettingsControllerTest.php) | 3 | 3 | 0 |
| Authentication | [ForgotPasswordTest.php](../../backend/tests/Feature/Auth/ForgotPasswordTest.php) | 4 | 4 | 0 |
| Authentication | [LoginTest.php](../../backend/tests/Feature/Auth/LoginTest.php) | 7 | 7 | 0 |
| Authentication | [LogoutTest.php](../../backend/tests/Feature/Auth/LogoutTest.php) | 2 | 2 | 0 |
| Authentication | [MeTest.php](../../backend/tests/Feature/Auth/MeTest.php) | 2 | 2 | 0 |
| Authentication | [RegisterTest.php](../../backend/tests/Feature/Auth/RegisterTest.php) | 5 | 5 | 0 |
| Authentication | [ResetPasswordTest.php](../../backend/tests/Feature/Auth/ResetPasswordTest.php) | 5 | 5 | 0 |
| Authentication | [TwoFactorChallengeTest.php](../../backend/tests/Feature/Auth/TwoFactorChallengeTest.php) | 5 | 5 | 0 |
| Public API | [ConfigurationTest.php](../../backend/tests/Feature/Public/ConfigurationTest.php) | 5 | 5 | 0 |
| Public API | [ShowPageTest.php](../../backend/tests/Feature/Public/ShowPageTest.php) | 5 | 5 | 0 |
| Smoke test | [SmokeTest.php](../../backend/tests/Feature/SmokeTest.php) | 1 | 1 | 0 |
| User / Budget | [CreateBudgetTest.php](../../backend/tests/Feature/User/Budget/CreateBudgetTest.php) | 12 | 12 | 0 |
| User / Budget | [IndexBudgetTest.php](../../backend/tests/Feature/User/Budget/IndexBudgetTest.php) | 6 | 6 | 0 |
| User / Category | [CreateCategoryTest.php](../../backend/tests/Feature/User/Category/CreateCategoryTest.php) | 6 | 6 | 0 |
| User / Category | [DeleteCategoryTest.php](../../backend/tests/Feature/User/Category/DeleteCategoryTest.php) | 7 | 7 | 0 |
| User / Category | [IndexCategoryTest.php](../../backend/tests/Feature/User/Category/IndexCategoryTest.php) | 4 | 4 | 0 |
| User / Category | [ShowCategoryTest.php](../../backend/tests/Feature/User/Category/ShowCategoryTest.php) | 5 | 5 | 0 |
| User / Category | [UpdateCategoryTest.php](../../backend/tests/Feature/User/Category/UpdateCategoryTest.php) | 7 | 7 | 0 |
| User / Dashboard | [ShowDashboardTest.php](../../backend/tests/Feature/User/Dashboard/ShowDashboardTest.php) | 9 | 9 | 0 |
| User / Settings | [ShowSettingsTest.php](../../backend/tests/Feature/User/Settings/ShowSettingsTest.php) | 5 | 5 | 0 |
| User / Settings | [UpdatePreferencesTest.php](../../backend/tests/Feature/User/Settings/UpdatePreferencesTest.php) | 5 | 5 | 0 |
| User / Settings | [UpdateProfileTest.php](../../backend/tests/Feature/User/Settings/UpdateProfileTest.php) | 8 | 8 | 0 |
| User / Transaction | [CreateTransactionTest.php](../../backend/tests/Feature/User/Transaction/CreateTransactionTest.php) | 8 | 8 | 0 |
| User / Transaction | [IndexTransactionTest.php](../../backend/tests/Feature/User/Transaction/IndexTransactionTest.php) | 9 | 9 | 0 |
| User / Wallet | [CreateWalletTest.php](../../backend/tests/Feature/User/Wallet/CreateWalletTest.php) | 6 | 6 | 0 |
| User / Wallet | [DeleteWalletTest.php](../../backend/tests/Feature/User/Wallet/DeleteWalletTest.php) | 6 | 6 | 0 |
| User / Wallet | [IndexWalletTest.php](../../backend/tests/Feature/User/Wallet/IndexWalletTest.php) | 4 | 4 | 0 |
| User / Wallet | [UpdateWalletTest.php](../../backend/tests/Feature/User/Wallet/UpdateWalletTest.php) | 7 | 7 | 0 |
| **Tổng cộng** | **79 file** | **439** | **434** | **5** |

## 11. Đánh giá chất lượng

### Điểm đã đạt

- Số lượng test case tăng từ 182 lên 439, mở rộng đáng kể độ bao phủ hành vi.
- 434/434 test đã thực thi đều pass, không có failure hoặc error.
- Phần lớn endpoint CRUD đã có đủ kiểm tra guest, user thường, admin, validation và not-found.
- Các ràng buộc ownership, dữ liệu trùng, dữ liệu liên quan khi xóa và quy tắc tính toán tài chính được kiểm tra rõ hơn.
- Các luồng bảo mật quan trọng như rate limit, 2FA, response không lộ secret và mật khẩu hash đã được bổ sung.

### Giới hạn hiện tại

- Còn 5 test case `todo`; cần triển khai trước khi coi toàn bộ 439 case là hoàn tất.
- Chưa đo code coverage vì môi trường PHP chưa có Xdebug hoặc PCOV.
- Unit test thực tế vẫn còn rất ít; phần lớn suite là Feature/API test.
- Chưa bao gồm performance/load test, concurrency test, security scan hoặc tích hợp hạ tầng production.
- Báo cáo phản ánh working tree tại thời điểm chạy; nên commit toàn bộ test tuần 4 rồi chạy lại để tạo mốc kết quả có thể truy vết tuyệt đối.

## 12. Kết luận

Bộ kiểm thử backend tuần 4 **đạt yêu cầu đối với các test đã thực thi**: 434 test case pass với 1.397 assertion, không có failure/error. So với báo cáo trước, nhóm đã bổ sung 257 test case, tập trung vào authentication, phân quyền, validation, ownership, truy vấn danh sách và các quy tắc nghiệp vụ tài chính.

Trước khi chốt tuần 4, nhóm cần hoàn thiện 5 test case đang `todo` và chạy lại toàn bộ suite. Sau khi các case này pass, bộ test có thể được dùng làm regression gate đầy đủ cho 439 hành vi hiện đã khai báo.

---

### Kết quả console

```text
..............................t.............................................
...............t............................t...............................
....................................................................t.......
.........................t..................................................
............................................................................
...........................................................

Tests:    5 todos, 434 passed (1397 assertions)
Duration: 5.44s
```
