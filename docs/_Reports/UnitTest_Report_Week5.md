# BÁO CÁO KIỂM THỬ — TUẦN 5

## 1. Thông tin chung

| Hạng mục | Giá trị |
|---|---|
| Dự án | KCPM |
| Tuần báo cáo | Tuần 5 |
| Phạm vi chính | Backend Laravel API và kiến trúc dữ liệu test dùng chung |
| Thư mục kiểm thử backend | [`backend/tests`](../../backend/tests/) |
| Thư mục dữ liệu test | [`docs/_DataTest`](../_DataTest/) |
| Framework kiểm thử backend | Pest 4 / PHPUnit 12 |
| Framework ứng dụng | Laravel 12.64.0 |
| Phiên bản PHP | PHP 8.4.7 |
| Công cụ tích hợp | Pest, CodeceptJS, Postman/Newman |
| Nhánh kiểm thử | `UnitTest` |
| Commit của lần chạy | `a4c30346071604992ca6acb49384e137440cd2cb` |
| Thời điểm thực thi gần nhất | 18/08/2026 22:05, múi giờ Asia/Ho_Chi_Minh (UTC+07:00) |
| Lệnh chạy backend | `cd backend && php artisan test --compact` |
| Lệnh kiểm tra dữ liệu JavaScript | `cd codeceptjs && npm run test:data` |
| Cấu hình test suite | [`backend/phpunit.xml`](../../backend/phpunit.xml) và [`backend/tests/Pest.php`](../../backend/tests/Pest.php) |

## 2. Nội dung thực hiện trong tuần 5

Trong tuần 5, nhóm tập trung chuẩn hóa và mở rộng test case theo mô hình **một nguồn dữ liệu dùng chung**. Thay vì khai báo lặp lại payload và kết quả mong đợi trong từng công cụ, các trường hợp kiểm thử được lưu thành JSON tại [`docs/_DataTest`](../_DataTest/) để Pest, CodeceptJS và Postman/Newman cùng sử dụng.

Các nội dung chính đã hoàn thành:

- Bổ sung 44 file dữ liệu nghiệp vụ với 736 test case có mã định danh duy nhất.
- Áp dụng Boundary Value Analysis, Robust Boundary Value Analysis, Equivalence Partitioning, RBAC và kiểm tra quy tắc nghiệp vụ.
- Xây dựng bộ nạp dữ liệu, sinh chuỗi biên, phân giải alias fixture và assertion dùng chung cho Pest.
- Tích hợp dữ liệu vào 36 operation backend, tương ứng 587 dòng dữ liệu được thực thi qua các feature test liên quan.
- Kiểm tra hợp đồng dữ liệu và ánh xạ 27 operation cho CodeceptJS và Postman, tương ứng 470 dòng dữ liệu.
- Bổ sung dữ liệu cho Budget và Menu; các file này đã qua kiểm tra hợp đồng nhưng chưa được nối toàn bộ vào runner thực thi.
- Giữ lại các test truyền thống để tạo lớp regression song song với các test data-driven mới.

## 3. Mục tiêu kiểm thử

- Tăng độ phủ các giá trị biên và phân vùng dữ liệu đầu vào.
- Kiểm tra nhất quán quyền truy cập của guest, user, admin và tài khoản có xác thực hai bước.
- Xác nhận response HTTP, JSON, validation error và thay đổi cơ sở dữ liệu.
- Giảm trùng lặp dữ liệu giữa Pest, CodeceptJS và Postman/Newman.
- Cho phép bổ sung một test case mới bằng cách thêm một dòng JSON có cấu trúc thống nhất.
- Phát hiện sớm dữ liệu sai cấu trúc, trùng `case_id`, alias thiếu hoặc truy cập file ngoài thư mục cho phép.

## 4. Kiến trúc dữ liệu test dùng chung

Mỗi file JSON đại diện cho một operation, ví dụ `admin/categories/create.json` hoặc `user/wallets/update.json`. Mỗi dòng test case gồm:

| Thành phần | Ý nghĩa |
|---|---|
| `case_id` | Mã test case duy nhất trên toàn bộ tập dữ liệu |
| `description` | Mô tả hành vi hoặc điều kiện cần kiểm tra |
| `actor` | Vai trò thực thi: admin, user, guest hoặc two-factor user |
| `preconditions` | Trạng thái fixture cần chuẩn bị trước khi gửi request |
| `request` | Method, endpoint, header, path, query và body |
| `expected` | HTTP status, JSON path, field không được xuất hiện, validation error và thay đổi database |
| `capture` | Giá trị cần lấy từ response để dùng cho request tiếp theo |

Các tiện ích quan trọng:

- [`backend/tests/Support/TestData.php`](../../backend/tests/Support/TestData.php) nạp JSON, kiểm tra hợp đồng, sinh dữ liệu `repeat` và phân giải alias như `@customer.id`.
- [`backend/tests/Support/TestResponseAssertions.php`](../../backend/tests/Support/TestResponseAssertions.php) kiểm tra status, JSON path, field bị cấm và validation error theo từng dòng dữ liệu.
- [`backend/tests/Unit/Support/TestDataTest.php`](../../backend/tests/Unit/Support/TestDataTest.php) bảo vệ cấu trúc và tính hợp lệ của toàn bộ kho dữ liệu.
- [`codeceptjs/support/test-data.js`](../../codeceptjs/support/test-data.js) cung cấp loader và alias resolver tương ứng cho JavaScript.
- [`docs/_Postman/Final.postman_collection.json`](../_Postman/Final.postman_collection.json) có script cấp collection để đọc dữ liệu, kiểm tra response và capture biến.

## 5. Quy mô dữ liệu test tuần 5

### 5.1. Tổng quan

| Chỉ số | Kết quả |
|---|---:|
| File JSON nghiệp vụ | 44 |
| Test case dữ liệu nghiệp vụ | 736 |
| File ví dụ hợp đồng | 1 |
| Dòng dữ liệu ví dụ | 2 |
| Tổng file JSON | 45 |
| Tổng dòng JSON | 738 |
| `case_id` duy nhất | 738/738 |
| File JSON lỗi cú pháp | 0 |
| Test case có alias fixture | 442 |
| Test case có bộ sinh dữ liệu lặp | 318 |
| Test case có chỉ thị capture | 161 |

### 5.2. Phân loại theo kỹ thuật thiết kế test

| Kỹ thuật | Ký hiệu | Số test case |
|---|---|---:|
| Boundary Value Analysis | BVA | 302 |
| Robust Boundary Value Analysis | RBVA | 87 |
| Equivalence Partitioning | EP | 209 |
| Role-Based Access Control | RBAC | 80 |
| Business rule | BUS | 50 |
| Chờ đặc tả hoặc hạ tầng thực thi | BLOCKED | 8 |
| **Tổng cộng** |  | **736** |

### 5.3. Phân bố theo actor và HTTP status mong đợi

| Actor | Số test case |
|---|---:|
| Admin | 442 |
| User | 204 |
| Guest | 88 |
| Two-factor user | 4 |

| HTTP status mong đợi | Số test case |
|---|---:|
| 200 OK | 235 |
| 201 Created | 164 |
| 401 Unauthorized | 37 |
| 403 Forbidden | 36 |
| 404 Not Found | 14 |
| 409 Conflict | 2 |
| 422 Unprocessable Content | 248 |
| Không gửi request, chỉ dùng làm ví dụ hợp đồng | 2 |

## 6. Danh sách operation và số test case dữ liệu

| Nhóm chức năng | Operation | File | Test case |
|---|---|---:|---:|
| Admin / Appearance | Update | 1 | 39 |
| Admin / Budget | Create, index, update | 3 | 45 |
| Admin / Category | Create, index, update | 3 | 60 |
| Admin / Menu | Create, index, parent options, update | 4 | 89 |
| Admin / Page | Create, index, update | 3 | 62 |
| Admin / Permission | Create, index, update | 3 | 39 |
| Admin / Role | Create, index, update, delete | 4 | 52 |
| Admin / Transaction | Create, index, update | 3 | 62 |
| Admin / User | Create, index, update | 3 | 44 |
| Authentication | Login, register, forgot/reset password, 2FA challenge | 5 | 45 |
| Public API | Configuration, page detail | 2 | 10 |
| User / Budget | Create | 1 | 15 |
| User / Category | Create, update, delete | 3 | 54 |
| User / Settings | Profile, preferences | 2 | 26 |
| User / Transaction | Create, index | 2 | 47 |
| User / Wallet | Create, update | 2 | 47 |
| **Tổng cộng** | **44 operation** | **44** | **736** |

## 7. Kết quả thực thi backend Pest/PHPUnit

### 7.1. Tổng quan

| Chỉ số | Kết quả |
|---|---:|
| Tổng số file test PHP | 81 |
| Unit test case | 8 |
| Feature/API test case | 973 |
| Tổng test case được khai báo | 981 |
| Test case đã thực thi và pass | 970 |
| Todo | 5 |
| Skipped | 6 |
| Failed | 0 |
| Errors | 0 |
| Tổng assertion | 12.732 |
| Tỷ lệ pass trên số test đã thực thi | **100%** |
| Tỷ lệ hoàn thành trên tổng test khai báo | **98,88%** |
| Thời gian chạy | 11,24 giây |
| Exit code | 0 |

**Kết quả chung:** toàn bộ 970 test case đã thực thi đều **PASSED**. Không có failed hoặc error; còn 5 case `todo` và 6 case `skipped` cần xử lý tiếp.

### 7.2. So sánh với tuần 4

| Chỉ số | Tuần 4 | Tuần 5 | Thay đổi |
|---|---:|---:|---:|
| File test PHP | 79 | 81 | **+2** |
| Tổng test case khai báo | 439 | 981 | **+542** |
| Test case pass | 434 | 970 | **+536** |
| Todo | 5 | 5 | 0 |
| Skipped | 0 | 6 | **+6** |
| Assertion | 1.397 | 12.732 | **+11.335** |
| Failed | 0 | 0 | 0 |
| Errors | 0 | 0 | 0 |

Số assertion tăng mạnh do test hợp đồng dữ liệu kiểm tra từng trường bắt buộc của tất cả dòng JSON, bên cạnh các assertion nghiệp vụ của feature test.

### 7.3. Kết quả theo module

| Nhóm chức năng | Test khai báo | Passed | Todo | Skipped | Assertion |
|---|---:|---:|---:|---:|---:|
| Unit và hợp đồng dữ liệu | 8 | 8 | 0 | 0 | 8.036 |
| Smoke test | 1 | 1 | 0 | 0 | 2 |
| Authentication | 50 | 50 | 0 | 0 | 366 |
| Public API | 10 | 10 | 0 | 0 | 58 |
| Admin / Appearance | 43 | 43 | 0 | 0 | 195 |
| Admin / Budget | 39 | 38 | 1 | 0 | 164 |
| Admin / Category | 89 | 89 | 0 | 0 | 486 |
| Admin / Dashboard | 4 | 4 | 0 | 0 | 21 |
| Admin / Menu | 37 | 35 | 2 | 0 | 161 |
| Admin / Page | 89 | 83 | 0 | 6 | 476 |
| Admin / Permission | 61 | 61 | 0 | 0 | 177 |
| Admin / Role | 76 | 76 | 0 | 0 | 245 |
| Admin / Transaction | 95 | 94 | 1 | 0 | 534 |
| Admin / User | 76 | 75 | 1 | 0 | 369 |
| API V1 controller regression | 15 | 15 | 0 | 0 | 47 |
| User / Budget | 18 | 18 | 0 | 0 | 59 |
| User / Category | 83 | 83 | 0 | 0 | 376 |
| User / Dashboard | 9 | 9 | 0 | 0 | 32 |
| User / Settings | 44 | 44 | 0 | 0 | 130 |
| User / Transaction | 64 | 64 | 0 | 0 | 449 |
| User / Wallet | 70 | 70 | 0 | 0 | 349 |
| **Tổng cộng** | **981** | **970** | **5** | **6** | **12.732** |

## 8. Chi tiết chức năng và test case đã bổ sung

### 8.1. Authentication — 45 test case dữ liệu

- **Login:** đăng nhập đúng/sai thông tin; email không tồn tại; thiếu email hoặc mật khẩu; email sai định dạng; chuyển sang bước 2FA khi tài khoản đã bật xác thực hai bước.
- **Register:** kiểm tra biên độ dài name và email; trường bắt buộc; email sai định dạng hoặc trùng; mật khẩu và xác nhận mật khẩu.
- **Forgot password:** gửi link cho email tồn tại; từ chối email lạ/sai định dạng; kiểm tra trường bắt buộc và throttle yêu cầu lặp lại.
- **Reset password:** token hợp lệ/không hợp lệ; token đã dùng; email không tồn tại; trường bắt buộc và xác nhận mật khẩu.
- **Two-factor challenge:** OTP hợp lệ/sai; recovery code dùng một lần; thiếu session; thiếu cả OTP và recovery code.

### 8.2. Public API — 10 test case dữ liệu

- Cấu hình public theo locale mặc định, tiếng Việt, tiếng Anh và locale không hỗ trợ.
- Chỉ trả menu active, đúng cây cha/con và đúng thứ tự hiển thị.
- Chỉ công khai page đã publish; ẩn draft/pending; xử lý slug không tồn tại và nested slug.

### 8.3. Admin / Appearance — 39 test case dữ liệu

- Phân quyền guest, user và admin.
- Kiểm tra các biên 0, 1, nominal, max - 1, max và max + 1 cho site name, site title, tagline và meta description.
- Kiểm tra logo nullable, kích thước từ 1 KB đến giới hạn 4.096 KB, quá giới hạn và MIME type không hỗ trợ.
- Xác nhận cập nhật nội dung không làm mất logo hiện có.

### 8.4. Admin / Budget — 45 test case dữ liệu

- Kiểm tra biên số tiền từ giá trị tối thiểu đến tối đa và hai phía ngoài biên.
- Validation user, category, period, status và trường bắt buộc.
- Ngăn trùng bộ `user-category-period`; chỉ dùng category hợp lệ của user hoặc category dùng chung.
- Index kiểm tra biên `page`/`per_page`, search, filter period, sort amount và phân quyền.
- Update cho phép giữ tổ hợp hiện tại, từ chối tổ hợp trùng và xử lý budget không tồn tại.

### 8.5. Admin / Category — 60 test case dữ liệu

- Kiểm tra biên name 1–120 ký tự và description tối đa 500 ký tự.
- Chuẩn hóa description rỗng; kiểm tra màu hex 3/6 ký tự và màu không hợp lệ.
- Ngăn trùng tên category global; validation status; phân quyền guest/user.
- Index chỉ trả category global, lọc active/inactive và kiểm tra biên phân trang.
- Update giữ quy tắc validation của create và xử lý ID không tồn tại.

### 8.6. Admin / Menu — 89 test case dữ liệu

- Kiểm tra biên title, URL, `sort_order` và canonical; validation target/status/parent.
- Tạo menu gốc, menu con kế thừa canonical và xử lý parent không tồn tại.
- Index kiểm tra phân trang, tìm title, lọc status/parent và sort.
- Parent options chỉ trả menu gốc, loại trừ menu được chỉ định và kiểm tra ID loại trừ.
- Update ngăn self-parent, kế thừa canonical mới, xử lý not-found và phân quyền.
- Hai trường hợp URL `javascript:` đang ở trạng thái chờ đặc tả hoặc kết nối runner đầy đủ.

### 8.7. Admin / Page — 62 test case dữ liệu

- Kiểm tra biên title, slug và meta title đến 255 ký tự; validation status/category.
- Sinh slug khi null/rỗng, thêm hậu tố khi trùng và từ chối slug nhập tay bị trùng.
- Chuẩn hóa/deduplicate tags; cập nhật quan hệ category; xử lý not-found.
- Index lọc page theo published, draft, pending và kiểm tra biên phân trang.
- Sáu trường hợp upload ảnh theo kích thước/MIME đang được skip vì adapter dữ liệu chưa dựng file upload runtime.

### 8.8. Admin / Permission và Role — 91 test case dữ liệu

- Permission: biên độ dài name/search, tên bắt buộc/duy nhất, sort, phân trang, not-found và phân quyền.
- Role: biên độ dài name/search; tạo/sync permission pivot; ID permission sai, trùng hoặc sai guard.
- Bảo vệ role hệ thống `admin` và `super-admin`; kiểm tra delete role thường.
- Index kiểm tra search, sort, phân trang và quyền truy cập.

### 8.9. Admin / Transaction — 62 test case dữ liệu

- Kiểm tra biên amount, note và label; chuẩn hóa giá trị rỗng; loại bỏ label trùng.
- Validation type/status, wallet/category không tồn tại và quyền sở hữu wallet/category.
- Index kiểm tra phân trang, filter income/expense và enum không hợp lệ.
- Update chuyển type/status, xử lý not-found và bảo vệ dữ liệu của user khác.

### 8.10. Admin / User — 44 test case dữ liệu

- Kiểm tra biên name/email; validation trường bắt buộc, định dạng email và email trùng.
- Kiểm tra role ID không tồn tại/trùng lặp và password confirmation.
- Index kiểm tra phân trang, lọc role và role không tồn tại.
- Update có/không đổi mật khẩu, đồng bộ role, giữ tính duy nhất email và xử lý not-found.

### 8.11. User / Budget — 15 test case dữ liệu

- Kiểm tra toàn bộ biên amount và period enum.
- Cho phép category global hoặc category active của chính user.
- Từ chối category inactive, category của user khác và budget trùng period.
- Kiểm tra guest và admin không được dùng endpoint dành riêng cho user.

### 8.12. User / Category — 54 test case dữ liệu

- Kiểm tra biên name/description, màu hex và chuẩn hóa description.
- Ngăn trùng với category global nhìn thấy; cho phép trùng tên private giữa hai user khác nhau.
- Category private mới luôn active; update giữ trạng thái inactive hiện tại.
- Chỉ chủ sở hữu được sửa/xóa; không sửa/xóa category global.
- Không xóa category đang được transaction hoặc budget tham chiếu; xử lý not-found.

### 8.13. User / Settings — 26 test case dữ liệu

- Profile kiểm tra biên name/email, email sai/trùng và bảo toàn xác minh khi email không đổi.
- Preferences chấp nhận VND, USD, EUR, GBP; từ chối độ dài/enum sai.
- Cập nhật preference lặp lại có tính idempotent; guest/admin bị từ chối theo route.

### 8.14. User / Transaction — 47 test case dữ liệu

- Kiểm tra biên amount, note và label; loại bỏ label trùng; chuẩn hóa category/note rỗng.
- Tạo income/expense hợp lệ và ép trạng thái đầu vào về `posted` theo quy tắc user.
- Từ chối type sai, wallet/category không tồn tại, category inactive hoặc tài nguyên của user khác.
- Index kiểm tra phân trang, filter type/status và chỉ trả transaction thuộc user hiện tại.

### 8.15. User / Wallet — 47 test case dữ liệu

- Kiểm tra biên tên ví 1–100 ký tự và opening balance từ `-9.999.999.999,99` đến `9.999.999.999,99`.
- Chuẩn hóa mã tiền tệ ba ký tự; từ chối độ dài không hợp lệ.
- Ngăn trùng tên ví trong cùng tài khoản nhưng cho phép user khác dùng cùng tên.
- Ví đầu tiên tự động là mặc định; chuyển default khi tạo/cập nhật ví khác.
- Không cho bỏ default nếu đó là ví mặc định duy nhất; bảo vệ ownership và xử lý not-found.

## 9. Kết quả kiểm tra CodeceptJS và Postman

### 9.1. CodeceptJS data contract

Lệnh `npm run test:data` đã chạy thành công:

| Chỉ số | Kết quả |
|---|---:|
| Test hỗ trợ dữ liệu | 8 |
| Passed | 8 |
| Failed | 0 |
| Skipped | 0 |
| Thời gian | 48,90 ms |

Các test xác nhận: collection script biên dịch được; biến dùng chung tồn tại; operation Postman được ánh xạ đúng file JSON; loader sinh chuỗi biên; alias lồng nhau/alias phẳng được phân giải; path traversal và alias thiếu bị từ chối; hợp đồng trường bắt buộc và `case_id` duy nhất được bảo đảm.

### 9.2. Phạm vi tích hợp runner

| Runner | Operation đã nối dữ liệu | Dòng dữ liệu tương ứng | Trạng thái xác nhận tuần 5 |
|---|---:|---:|---|
| Pest feature test | 36/44 | 587/736 | Đã chạy trong full backend suite |
| CodeceptJS | 27/44 | 470/736 | Loader/mapping pass; chưa chạy lại toàn bộ trình duyệt E2E |
| Postman/Newman | 27/44 | 470/736 | Collection script/mapping pass; chưa chạy Newman toàn bộ dữ liệu |

Số liệu 736 là quy mô thiết kế test data, không được cộng trực tiếp vào 981 test Pest vì một phần dữ liệu chưa nối runner và Pest vẫn có các regression test không data-driven.

## 10. Test case chưa hoàn tất

### 10.1. Năm test case `todo`

| Module | Test case | Nguyên nhân |
|---|---|---|
| Admin / Budget | Tìm budget theo ID | Service đang gọi `orWhereKey` không tồn tại trên Eloquent Builder |
| Admin / Transaction | Tìm transaction theo ID | Service đang gọi `orWhereKey` không tồn tại trên Eloquent Builder |
| Admin / User | Tìm user theo ID | Service đang gọi `orWhereKey` không tồn tại trên Eloquent Builder |
| Admin / Menu | Chặn URL có scheme `javascript:` | Quy tắc validation URL thực thi chưa được hoàn thiện |
| Admin / Menu | Chặn chu trình cha/con gián tiếp | Request mới ngăn self-parent, chưa phát hiện descendant cycle |

### 10.2. Sáu test case `skipped`

Các case `ADM-PAGE-CREATE-BLOCKED-020` đến `ADM-PAGE-CREATE-BLOCKED-025` kiểm tra ảnh page ở các mốc 1 KB, 1.024 KB, 2.047 KB, 2.048 KB, 2.049 KB và MIME type sai. Các case đã có dữ liệu nhưng bị skip vì adapter data-driven hiện chưa chuyển mô tả file JSON thành đối tượng upload giả của Laravel.

## 11. Đánh giá chất lượng

### Điểm đạt được

- 100% test backend thực thi thành công, không có failure hoặc error.
- Test case tăng từ 439 lên 981, trong khi vẫn giữ thời gian chạy khoảng 11 giây.
- Dữ liệu có mã định danh duy nhất và hợp đồng đầy đủ, giúp truy vết case giữa ba công cụ.
- Các giá trị biên dài được sinh ở runtime, tránh lưu chuỗi lặp lớn và giảm sai lệch khi sao chép.
- Alias fixture giúp dữ liệu JSON độc lập với ID được tạo ngẫu nhiên trong database test.
- Kiểm tra response và thay đổi database được mô tả thống nhất, giảm assertion viết lặp.

### Hạn chế và công việc tiếp theo

- Hoàn thiện 5 case `todo` liên quan đến tìm kiếm ID, URL nguy hiểm và chu trình menu.
- Xây adapter file upload để chạy 6 case page đang skip.
- Nối 8 operation Budget/Menu còn lại vào Pest và mở rộng ánh xạ CodeceptJS/Postman từ 27 lên đủ 44 operation.
- Chạy full CodeceptJS E2E và Newman với môi trường frontend/backend hoạt động để xác nhận hành vi end-to-end, không chỉ hợp đồng và mapping.
- Duy trì kiểm tra hợp đồng dữ liệu trong CI để ngăn JSON sai cấu trúc hoặc trùng `case_id` được merge.

## 12. Kết luận

Trong tuần 5, nhóm đã chuyển bộ kiểm thử từ mô hình test case rời rạc sang kiến trúc data-driven dùng chung cho Pest, CodeceptJS và Postman. Kho dữ liệu hiện có 736 test case nghiệp vụ, bao phủ giá trị biên, phân vùng tương đương, phân quyền và quy tắc nghiệp vụ của 16 nhóm chức năng.

Backend suite đạt **970/970 test đã thực thi pass**, không có failed/error và thực hiện **12.732 assertions**. Kiến trúc mới tạo nền tảng để nhóm tiếp tục tăng độ phủ mà không phải sao chép dữ liệu giữa các công cụ; phần còn lại cần ưu tiên là hoàn thiện 5 todo, 6 case upload bị skip và nối đầy đủ các operation chưa được runner sử dụng.
