# BÁO CÁO COVERAGE CÁ NHÂN — AUTH, PUBLIC, ADMIN APPEARANCE VÀ ADMIN DASHBOARD

## 1. Thông tin chung

| Nội dung | Kết quả |
| --- | --- |
| Ngày xác nhận | 26/08/2026 |
| Phạm vi | Auth, Public, Admin Appearance, Admin Dashboard |
| Framework kiểm thử | Pest 4 / Laravel 12 |
| Test data | 12 file JSON, 111 test case dữ liệu |
| Tổng kết chạy Pest | 120 passed, 0 failed, 0 todo, 0 blocked |
| Assertions | 13.938 |
| Thời gian | 2,69 giây |

Decision Table cho Login và 2FA chỉ được dùng trong quá trình phân tích để xác định các tổ hợp còn thiếu, đúng theo yêu cầu và không được đưa vào tài liệu bàn giao.

## 2. Test data đã refactor

| Module | JSON test data | Số test case dữ liệu |
| --- | --- | ---: |
| Auth | `login.json`, `register.json`, `two-factor-challenge.json`, `logout.json`, `me.json`, `forgot-password.json`, `reset-password.json` | 56 |
| Public | `configuration.json`, `pages-show.json` | 10 |
| Admin Appearance | `show.json`, `update.json` | 42 |
| Admin Dashboard | `show.json` | 3 |
| **Tổng** | **12 file JSON** | **111** |

Các file dùng chung contract gồm `case_id`, `description`, `actor`, `preconditions`, `request` và `expected`. Pest nạp dữ liệu bằng `Tests\Support\TestData`, resolve generator/fixture alias, sau đó kiểm tra HTTP status, JSON path, validation error và thay đổi cơ sở dữ liệu.

## 3. Test case bổ sung từ Decision Table

### 3.1. Login

| Case ID | Tổ hợp được bổ sung | Kết quả |
| --- | --- | --- |
| `AUTH-LOGIN-SUBMIT-EP-008` | Credentials hợp lệ nhưng `remember` không phải boolean | 422, lỗi `remember` |
| `AUTH-LOGIN-SUBMIT-BUS-009` | Credentials hợp lệ, có 2FA secret nhưng chưa confirm 2FA | Đăng nhập trực tiếp, không mở challenge |

Login đồng thời bao phủ credentials đúng, sai mật khẩu, email không tồn tại, thiếu email, thiếu password, email sai định dạng, confirmed 2FA và rate limiting sau nhiều lần thất bại.

### 3.2. Two-factor authentication

| Case ID | Tổ hợp được bổ sung | Kết quả |
| --- | --- | --- |
| `AUTH-2FA-VERIFY-EP-007` | Có challenge session nhưng user không còn 2FA secret | 422, không đăng nhập |
| `AUTH-2FA-VERIFY-EP-008` | Gửi đồng thời authenticator code và recovery code | Recovery code được ưu tiên và bị kiểm tra |

2FA đồng thời bao phủ OTP hợp lệ/sai, recovery code hợp lệ, recovery code đã dùng, thiếu challenge session và thiếu cả hai phương thức xác minh.

## 4. Boundary Value Analysis

### 4.1. Register

| Trường | Rule | Giá trị đã kiểm tra |
| --- | --- | --- |
| Name | Bắt buộc, tối đa 255 ký tự | 1, 2, 128, 254, 255; thiếu, rỗng, 256 |
| Email | Email hợp lệ, unique, tối đa 255 ký tự | Nominal, 254, 255; thiếu, sai định dạng, 256, trùng email |
| Password | Password mặc định tối thiểu 8 ký tự, confirmed | 7, 8, 9; thiếu password, thiếu confirmation, confirmation không khớp |

Ba case BVA password được bổ sung trong lần refactor hiện tại:

- `AUTH-REG-CREATE-BVA-019`: từ chối 7 ký tự.
- `AUTH-REG-CREATE-BVA-020`: chấp nhận 8 ký tự.
- `AUTH-REG-CREATE-BVA-021`: chấp nhận 9 ký tự.

Happy path còn xác nhận user được tạo, password được hash, user được đăng nhập và event `Registered` được dispatch.

### 4.2. Admin Appearance

| Trường | Rule | Giá trị đã kiểm tra |
| --- | --- | --- |
| Site name | Nullable, tối đa 255 ký tự | null, rỗng, 1, 128, 254, 255, 256 |
| Site title | Nullable, tối đa 255 ký tự | null, rỗng, 1, 128, 254, 255, 256 |
| Tagline | Nullable, tối đa 255 ký tự | null, rỗng, 1, 128, 254, 255, 256 |
| Meta description | Nullable, tối đa 500 ký tự | null, rỗng, 1, 250, 499, 500, 501 |
| Logo upload | Nullable, tối đa 4096 KB | null, 1, 100, 4095, 4096, 4097 KB |

Ngoài BVA, logo còn được kiểm tra MIME type không hỗ trợ và nhánh cập nhật text không upload logo mới phải giữ nguyên logo hiện tại.

## 5. Coverage các flow Auth

| Flow | Nội dung được kiểm tra |
| --- | --- |
| Login | Credentials đúng/sai, validation, remember flag, confirmed/unconfirmed 2FA, rate limiting, response không lộ trường nhạy cảm |
| Register | BVA name/email/password, unique email, confirmation, password hash, authentication sau đăng ký, event `Registered` |
| 2FA | OTP, recovery code, one-time consumption, challenge session, thiếu secret, precedence khi gửi đồng thời hai phương thức |
| Logout | User đăng nhập logout thành công, session không còn truy cập được `/auth/me`, guest bị 401 |
| Me | User nhận đúng account data, guest bị 401, response không lộ secret xác thực |
| Forgot password | Email tồn tại/không tồn tại, required/format, notification, token record, throttle yêu cầu lặp |
| Reset password | Token hợp lệ/sai/đã dùng, required fields, confirmation, email không tồn tại, password hash và event reset |

## 6. Public API

| Endpoint | Nội dung được kiểm tra |
| --- | --- |
| `GET /api/v1/public/configuration` | Locale mặc định, `vi`, `en`, locale không hỗ trợ, chỉ menu active đúng thứ tự, cache header |
| `GET /api/v1/public/pages/{slug}` | Published page, draft/pending bị 404, slug không tồn tại, nested slug, cache header |

## 7. Admin Appearance và Admin Dashboard

### Admin Appearance

- Admin được xem và cập nhật cấu hình.
- Guest nhận 401; regular user nhận 403.
- Response show có `languages`, `logos`, `general` và đúng default locale.
- Update xác nhận dữ liệu được lưu trong bảng `settings` và file logo hợp lệ được tạo.

### Admin Dashboard

- Admin nhận 200 cùng cấu trúc `stats`, `monthlyFlow`, `topExpenseCategories`, `recentTransactions`.
- Guest nhận 401; regular user nhận 403.
- `monthlyFlow` luôn có sáu tháng.
- Aggregate test xác nhận riêng posted income/expense trong tháng, net, pending count, active global categories, active budgets và top expense category.
- Pending transaction và transaction ngoài tháng không bị cộng nhầm vào các metric posted của tháng.

## 8. Kết quả Passed / Failed / Todo / Blocked

| Nhóm chạy | Passed | Failed | Todo | Blocked | Assertions |
| --- | ---: | ---: | ---: | ---: | ---: |
| Auth | 57 | 0 | 0 | 0 | 427 |
| Public | 10 | 0 | 0 | 0 | 58 |
| Admin Appearance | 42 | 0 | 0 | 0 | 197 |
| Admin Dashboard | 4 | 0 | 0 | 0 | 24 |
| Shared test-data contract | 7 | 0 | 0 | 0 | 13.232 |
| **Tổng** | **120** | **0** | **0** | **0** | **13.938** |

Không tìm thấy test đánh dấu `todo`, `skip`, `blocked_reason` hoặc defect chưa xử lý trong phạm vi này.

## 9. Kỹ thuật test đã áp dụng

| Kỹ thuật | Áp dụng |
| --- | --- |
| Data-driven testing | Một JSON contract được dùng làm dataset cho Pest |
| Decision Table | Phân tích trạng thái credentials, confirmed 2FA, challenge session, OTP và recovery code |
| Equivalence Partitioning | Credentials, locale, page status, MIME type, token và role hợp lệ/không hợp lệ |
| BVA / Robust BVA | Register name/email/password; Appearance text length và logo size |
| RBAC / Authorization | Admin, regular user, authenticated user và guest |
| State-transition testing | Login sang 2FA challenge, recovery code được consume, logout làm mất session, reset token chỉ dùng một lần |
| Negative testing | Required fields, invalid format, invalid enum/value, unauthorized/forbidden/not found |
| Security assertions | Password hash, response không lộ password/2FA secret/recovery codes, rate limiting |
| Database/event assertions | User/settings/token state và các event authentication liên quan |

## 10. Giới hạn code coverage

Môi trường hiện tại không cài Xdebug hoặc PCOV nên Pest không thể thu thập line/branch coverage tự động. Báo cáo này thể hiện functional coverage dựa trên endpoint, validation rule, authorization, state transition và nhánh nghiệp vụ đã được thực thi; không quy đổi thành phần trăm line coverage.

## 11. Lệnh và kết quả xác nhận

```bash
cd backend
php artisan test --compact \
  tests/Feature/Auth \
  tests/Feature/Public \
  tests/Feature/Admin/Appearance \
  tests/Feature/Admin/Dashboard \
  tests/Unit/Support/TestDataTest.php
```

Kết quả:

```text
Tests:    120 passed (13938 assertions)
Duration: 2.69s
```

## 12. Kết luận

Phạm vi được giao đã có 111 test case JSON data-driven. Các khoảng trống từ Decision Table của Login/2FA, BVA của Register/Admin Appearance, toàn bộ flow Auth chính, Public API và quyền truy cập/aggregate response của Admin Dashboard đều đã được kiểm chứng bằng Pest. Kết quả cuối cùng là 120 passed, 0 failed, 0 todo và 0 blocked.
