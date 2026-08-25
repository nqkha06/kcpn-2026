# BÁO CÁO COVERAGE CÁ NHÂN — TRANSACTIONS, WALLETS VÀ CATEGORIES

## 1. Thông tin chung

| Nội dung | Kết quả |
| --- | --- |
| Ngày xác nhận | 26/08/2026 |
| Phạm vi | Admin Transactions, User Transactions, User Wallets, User Categories |
| Framework kiểm thử | Pest 4 / Laravel 12 |
| Kỹ thuật | Data-driven testing, Equivalence Partitioning, BVA, Robust BVA, RBAC/Ownership, Decision Table |
| Kết quả chạy chung | 321 passed, 1 todo, 0 failed |
| Assertions | 14.980 |
| Thời gian | 3,70 giây |

Decision Table cho Transaction chỉ được sử dụng trong quá trình phân tích để xác định tổ hợp còn thiếu, đúng theo yêu cầu và không được đưa vào tài liệu bàn giao.

## 2. Phạm vi JSON test data đã refactor

| Module | JSON test data | Số test case dữ liệu |
| --- | --- | ---: |
| Admin Transactions | `admin/transactions/create.json`, `index.json`, `update.json`, `options.json` | 67 |
| User Transactions | `user/transactions/create.json`, `index.json` | 48 |
| User Wallets | `user/wallets/create.json`, `update.json` | 47 |
| User Categories | `user/categories/create.json`, `update.json`, `delete.json` | 54 |
| **Tổng** | **11 file JSON** | **216** |

Các file dùng chung một contract gồm `case_id`, `description`, `actor`, `preconditions`, `request` và `expected`. Pest nạp dữ liệu bằng `Tests\Support\TestData`, resolve generator/fixture alias, sau đó kiểm tra HTTP status, JSON path, validation error và thay đổi cơ sở dữ liệu.

## 3. Test case bổ sung từ Decision Table

Các chiều được đối chiếu gồm Wallet, Category, Type, Status và Ownership. Sau khi loại các tổ hợp tương đương và không hợp lệ theo nghiệp vụ, ba khoảng trống có ý nghĩa đã được bổ sung:

| Case ID | Tổ hợp được bổ sung | Kết quả |
| --- | --- | --- |
| `ADM-TXN-CREATE-DT-034` | Wallet của user được chọn + private category của chính user + income + pending | 201 Created |
| `ADM-TXN-CREATE-DT-035` | Wallet của user được chọn + không category + expense + cancelled | 201 Created |
| `USR-TXN-CREATE-DT-035` | Wallet của user đăng nhập + private category của chính user + expense + posted do hệ thống áp dụng | 201 Created |

## 4. Boundary Value Analysis

### 4.1. Amount

Rule: `between:0.01,9999999999.99`.

| Nhóm | Giá trị đã kiểm tra |
| --- | --- |
| Biên hợp lệ | 0.01, 0.02, 5.000.000.000, 9.999.999.999,98, 9.999.999.999,99 |
| Ngoài biên | 0, 10.000.000.000 |
| Áp dụng | Admin Transaction create/update và User Transaction create |

### 4.2. Wallet name

Rule: bắt buộc, chuỗi, tối đa 100 ký tự.

| Nhóm | Độ dài đã kiểm tra |
| --- | --- |
| Hợp lệ | 1, 2, 50, 99, 100 |
| Không hợp lệ | thiếu, rỗng, 101 |
| Áp dụng | User Wallet create/update |

### 4.3. Opening balance

Rule: `between:-9999999999.99,9999999999.99`.

| Nhóm | Giá trị đã kiểm tra |
| --- | --- |
| Biên hợp lệ | -9.999.999.999,99; -9.999.999.999,98; 0; 9.999.999.999,98; 9.999.999.999,99 |
| Ngoài biên | -10.000.000.000; 10.000.000.000 |
| Áp dụng | User Wallet create/update |

### 4.4. Category name

Rule: bắt buộc, chuỗi, tối đa 120 ký tự.

| Nhóm | Độ dài đã kiểm tra |
| --- | --- |
| Hợp lệ | 1, 2, 60, 119, 120 |
| Không hợp lệ | thiếu, rỗng, 121 |
| Áp dụng | User Category create/update |

## 5. Ownership và phân quyền

| Luồng | Trường hợp đã kiểm tra |
| --- | --- |
| User Transactions | Tạo/list dữ liệu của mình; không tạo được transaction với wallet hoặc private category của user khác; không dùng được category inactive; danh sách chỉ trả transaction thuộc user đăng nhập |
| User Wallets | Tạo/cập nhật/xóa wallet của mình; không cập nhật hoặc xóa wallet của user khác; tên wallet chỉ unique trong cùng tài khoản |
| User Categories | Tạo/xem/cập nhật/xóa private category của mình; không xem/cập nhật/xóa private category của user khác; không sửa/xóa global category |
| Admin Transactions | Admin được thao tác transaction của mọi user nhưng wallet phải thuộc user được chọn; private category phải thuộc user được chọn; guest bị 401 và regular user bị 403 tại admin endpoint |

## 6. Endpoint options của Admin Transactions

Endpoint `GET /api/v1/admin/transactions/options` đã được chuyển sang shared JSON data với ba case:

- Admin nhận đầy đủ `users`, `wallets`, `categories`, `types` và `statuses`.
- Guest nhận 401.
- Regular user nhận 403.

Happy path còn xác nhận dữ liệu fixture của user, wallet và category xuất hiện trong response; `types` gồm `income`, `expense`; `statuses` gồm `posted`, `pending`, `cancelled`.

## 7. Coverage theo module

Môi trường hiện tại không cài Xdebug hoặc PCOV nên Pest không thể thu thập line/branch coverage tự động. Bảng dưới đây thể hiện functional coverage dựa trên endpoint, validation rule, authorization và nhánh nghiệp vụ đã được thực thi; không quy đổi thành phần trăm line coverage.

| Module | Endpoint/nhánh được bao phủ | Kết quả Pest |
| --- | --- | ---: |
| Admin Transactions | index, create, show, update, delete, options; filter/sort/pagination; BVA amount; type/status; wallet/category ownership; RBAC | 96 passed, 1 todo, 563 assertions |
| User Transactions | index, create; search/filter/date range/sort/pagination; BVA amount; wallet/category ownership; posted balance effect | 65 passed, 460 assertions |
| User Wallets | index, create, update, delete; BVA name/balance; default wallet; duplicate name; ownership; current balance | 70 passed, 349 assertions |
| User Categories | index, create, show, update, delete; BVA name/description; color; duplicate visibility; ownership; referenced category conflict | 83 passed, 376 assertions |
| Shared test-data contract | JSON contract, generator, alias, uniqueness và response assertions | 7 passed, 13.232 assertions |

## 8. TODO và defect

### TODO: tìm Admin Transaction theo ID

- Test: `an admin can search transactions by id`.
- Vị trí: `tests/Feature/Admin/Transaction/IndexTransactionTest.php`.
- Trạng thái: vẫn được đánh dấu `todo`, đúng yêu cầu tiếp tục theo dõi.
- Nguyên nhân: `AdminTransactionService::paginate()` gọi `orWhereKey()`, trong khi Eloquent Builder của Laravel 12 có `whereKey()` và `whereKeyNot()` nhưng không định nghĩa `orWhereKey()`.
- Ảnh hưởng: tìm kiếm Admin Transaction bằng chuỗi số có nguy cơ phát sinh lỗi thay vì trả transaction theo ID.
- Hướng xử lý đề xuất: thay nhánh tìm ID bằng điều kiện `orWhere($model->getQualifiedKeyName(), (int) $search)` hoặc một biểu thức Eloquent tương đương, sau đó bỏ `todo` và chạy lại test.

Không phát hiện failure mới trong phạm vi chạy. Không sửa defect trên vì yêu cầu hiện tại là tiếp tục theo dõi TODO.

## 9. Lệnh và kết quả xác nhận

Lệnh chạy toàn bộ phạm vi:

```bash
cd backend
php artisan test --compact \
  tests/Feature/Admin/Transaction \
  tests/Feature/User/Transaction \
  tests/Feature/User/Wallet \
  tests/Feature/User/Category \
  tests/Unit/Support/TestDataTest.php
```

Kết quả:

```text
Tests:    1 todo, 321 passed (14980 assertions)
Duration: 3.70s
```

Thử thu thập code coverage tự động:

```bash
php artisan test --compact --coverage tests/Feature/Admin/Transaction/OptionsTransactionTest.php
```

Môi trường trả về:

```text
ERROR  Code coverage driver not available. Did you install Xdebug or PCOV?
```

## 10. Kết luận

Phạm vi được giao đã có 216 test case JSON data-driven và được kiểm chứng cùng các test hiện hữu. Tất cả 321 test đã thực thi đều pass; một TODO tìm Admin Transaction theo ID vẫn được giữ để theo dõi defect `orWhereKey()`. BVA, ownership, các tổ hợp còn thiếu từ Decision Table và endpoint options của Admin Transactions đều đã được bao phủ bằng Pest.
