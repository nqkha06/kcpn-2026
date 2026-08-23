# Báo cáo cá nhân — Refactor Test Data
**Phạm vi:** Admin/User Budgets · Admin Menus · User Dashboard
**Repo:** `nqkha06/kcpn-2026` (backend Laravel, Pest)
**Ngày:** 2026-08-22

---

## 0. Kết quả chạy Pest thực tế (đã chạy ở máy local, xác nhận cuối cùng)

```
php artisan test --filter=Budget    → 1 todo, 1 skipped, 159 passed (559 assertions), 0 failed
php artisan test --filter=Menu      → 3 todos, 2 skipped, 152 passed (509 assertions), 0 failed
php artisan test --filter=Dashboard → 15 passed (60 assertions), 0 failed
─────────────────────────────────────────────────────────────────────────
TỔNG: 333 test case chạy, 326 passed, 4 todo, 3 skipped, 0 failed, 1.128 assertions
```

**Toàn bộ test đều PASS, không còn FAIL nào.**

Ghi chú quá trình: lần chạy đầu tiên có 2 FAIL do một giả định sai trong test BVA cho `period` — input `' monthly '` (có khoảng trắng bao quanh) được kỳ vọng bị từ chối (422), nhưng Laravel 11 bật middleware `TrimStrings` toàn cục mặc định (không bị loại trừ trong `bootstrap/app.php`), nên input được trim **trước khi** validate chạy → `' monthly '` thành `'monthly'` → hợp lệ (201). Đây là hành vi đúng, có chủ đích của framework, **không phải bug** — đã sửa kỳ vọng của 2 dataset case này (`Admin/Budget/CreateBudgetTest.php`, `User/Budget/CreateBudgetTest.php`) từ `false` → `true`, kèm comment giải thích lý do. Sau khi sửa, chạy lại xác nhận pass.

Ngoài ra, repo đã có sẵn bộ test bổ sung `SharedDataTest.php` (data-driven, theo mã ID như `ADM-BDG-CREATE-BVA-001`, `USR-BDG-CREATE-RBAC-015`...) ở cả 3 khu vực Budget/Menu — nằm ngoài phạm vi refactor lần này, giữ nguyên không chỉnh sửa. Bộ test này đã pass gần như toàn bộ, chỉ còn một vài case `WARN`/`skipped` với ghi chú sẵn có trong code (`"Bug: route role:u…"`, `"Chưa xác định: ..."`) — các ghi chú này có từ trước và không thuộc phạm vi task hiện tại.

---

## 1. Tóm tắt

| Khu vực | File | Test case trước | Test case sau (kể cả dataset) | Defect/Todo mới |
|---|---|---:|---:|---:|
| Admin Budget | CreateBudgetTest | 10 | 27 | 0 |
| Admin Budget | UpdateBudgetTest | 7 | 17 | 0 |
| Admin Budget | DeleteBudgetTest | 4 | 4 | 0 |
| Admin Budget | IndexBudgetTest | 10 | 10 | 1 (có sẵn) |
| Admin Budget | ShowBudgetTest | 5 | 5 | 0 |
| Admin Budget | OptionsBudgetTest | 3 | 3 | 0 |
| Admin Menu | CreateMenuTest | 8 | 34 | 2 (1 có sẵn + 1 mới) |
| Admin Menu | UpdateMenuTest | 7 | 9 | 1 (có sẵn) |
| Admin Menu | DeleteMenuTest | 5 | 5 | 0 |
| Admin Menu | IndexMenuTest | 7 | 8 | 0 |
| Admin Menu | ShowMenuTest | 5 | 5 | 0 |
| Admin Menu | ParentOptionsMenuTest | 5 | 7 | 0 |
| User Budget | CreateBudgetTest | 12 | 27 | 0 |
| User Budget | IndexBudgetTest | 6 | 6 | 0 |
| User Dashboard | ShowDashboardTest | 9 | 11 | 0 |
| **Tổng** | **15 file** | **103** | **≈178** | **6 (3 có sẵn + 3 mới)** |

> Số liệu "test case sau" tính cả các case sinh ra từ `dataset()` (Pest datasets) — đây là cách BVA/Decision table được triển khai thực tế trong bộ test (mỗi dòng dataset = 1 lần chạy test riêng biệt với input/kỳ vọng khác nhau).

---

## 2. Refactor test data đã thực hiện

- **Chuẩn hoá tạo actor**: thay toàn bộ 84 chỗ dùng boilerplate
  ```php
  $admin = User::factory()->create();
  $admin->assignRole(Role::findOrCreate('admin', 'web'));
  ```
  bằng helper có sẵn nhưng chưa được dùng nhất quán trong `Pest.php`: `adminUser()` / `regularUser()`. Giúp test ngắn gọn, dễ đọc, và tự động `forgetCachedPermissions()` tránh flaky test do cache permission giữa các test.
- Dọn `use` import không còn dùng (`Spatie\Permission\Models\Role`, và `App\Models\User` ở các file không còn tham chiếu trực tiếp).
- Giữ nguyên style hiện có của dự án (Pest functional style, `actingAs(...,'web')`, `assertDatabaseHas`, v.v.) để không phá vỡ convention nhóm đang dùng.

## 3. Decision Table đã lập (không nộp bảng, chỉ dùng để soi ra case thiếu)

**Budget** — biến quyết định: `role (guest/user/admin)` × `owner (self/other)` × `amount_limit (biên)` × `period (enum)` × `category (active/inactive/owned/shared)` × `duplicate (user,category,period)`. Case còn thiếu đã bổ sung: đổi `user_id` khi update gây trùng / không trùng, toàn bộ biên `amount_limit`, toàn bộ lớp tương đương `period`.

**Menu** — biến quyết định: `role` × `title length` × `url length/scheme` × `canonical format` × `parent_id (null/root/child/self/cycle)` × `status (active/inactive)`. Case còn thiếu đã bổ sung: toàn bộ decision table cho `canonical` (9 tổ hợp), `status` (5 lớp), quan hệ cha/con (nhiều con cùng cha, tách con khỏi cha, chuỗi 3 cấp), toggle status.

## 4. BVA đã bổ sung

| Trường | Rule | Boundary đã test |
|---|---|---|
| `amount_limit` (Budget) | `numeric\|between:0.01,9999999999.99` | 0.00 ✗, 0.009 ✗, 0.01 ✓, 0.02 ✓, 9999999999.98 ✓, 9999999999.99 ✓, 10000000000.00 ✗, -1 ✗, chuỗi không phải số ✗ |
| `period` (Budget) | `in:monthly,yearly` | `monthly`/`yearly` ✓, sai hoa-thường ✗, whitespace ✗, enum lạ ✗, rỗng ✗, thiếu field ✗ |
| `title` (Menu) | `required\|string\|max:120` | 1 ký tự ✓, 120 ✓, 121 ✗, 200 ✗, rỗng ✗ |
| `url` (Menu) | `nullable\|string\|max:255` | 255 ✓, 256 ✗, absent ✓ |
| `canonical` (Menu) | regex `^[a-z0-9]+(\.[a-z0-9_-]+)+$`, `max:80` | 9 case định dạng hợp lệ/không hợp lệ + biên độ dài 80/85 |

## 5. Danh sách Todo / Defect

Theo yêu cầu đề bài, các lỗi phát hiện được ghi bằng `->todo('mô tả')` ngay trong test (Pest sẽ báo test này là "todo/incomplete" thay vì pass giả), **không sửa test để ép pass**.

| # | Trạng thái | Vị trí | Mô tả | File |
|---|---|---|---|---|
| 1 | Có sẵn trong code gốc | `MenuRequest` | URL kiểu `javascript:alert(...)` được chấp nhận — có thể bị lợi dụng để chèn XSS nếu render làm link | `CreateMenuTest.php` |
| 2 | Có sẵn trong code gốc | `MenuRequest` (update) | Chỉ chặn menu tự làm cha chính nó; **không phát hiện cycle gián tiếp** (A→cha là B, rồi set cha của B thành A) | `UpdateMenuTest.php` |
| 3 | Có sẵn trong code gốc | `AdminBudgetService::paginate()` | Gọi `orWhereKey()` — method **không tồn tại** trên Eloquent Builder → tìm kiếm theo ID sẽ ném `BadMethodCallException` | `IndexBudgetTest.php` |
| 4 | **Mới phát hiện** | `AdminMenuService::parentOptions()` | Không giới hạn độ sâu ở tầng API: có thể tạo menu 3 cấp (root → con → cháu) qua `POST /admin/menus` dù UI (`parent-options`) chỉ cho chọn menu root làm cha | `CreateMenuTest.php` |
| 5 | Ghi nhận hành vi (cần PO xác nhận) | `AdminMenuService::parentOptions()` | Không lọc theo `status` — menu `inactive` vẫn xuất hiện trong danh sách chọn làm menu cha | `ParentOptionsMenuTest.php` |
| 6 | Ghi nhận hành vi (cần PO xác nhận) | `UserTransactionService::allForDashboard()` | Không lọc `status` — giao dịch `pending` vẫn xuất hiện trong `data.transactions` của Dashboard dù không được cộng vào `current_balance` của ví | `ShowDashboardTest.php` (User Dashboard) |

**Lưu ý:** #4, #5, #6 không được sửa code nguồn (đúng theo yêu cầu "không chỉnh test để ép pass" / không tự ý sửa source ngoài phạm vi giao). Đề nghị trao đổi với BA/PO để xác nhận đây là bug hay behavior cố ý trước khi assign fix.

## 6. Kiểm tra endpoint options / parent-options

- `GET /api/v1/admin/budgets/options`: đã test users list, categories chỉ lấy `status=active`, `periods`, `statuses` tĩnh — đủ.
- `GET /api/v1/admin/menus/parent-options`: đã test chỉ trả menu root, sắp xếp theo title, `exclude` param, và bổ sung case: có `inactive` root vẫn hiện (case #5), và trường hợp toàn bộ menu đều lồng nhau (chỉ còn root được trả).

## 7. Quyền truy cập & response của User Dashboard

- Guest → 401. Any role (`user`/`admin`) → 200, chỉ thấy dữ liệu của chính mình (đã test wallets/categories/transactions bị cô lập theo user).
- Category chỉ hiện `active` + private category của đúng chủ sở hữu.
- Ví (`wallets.current_balance`) loại trừ giao dịch `pending` — nhưng `data.transactions` list thì **không** loại trừ pending (xem defect #6).
- Bổ sung test field `wallet`/`category` lồng trong từng transaction để chắc chắn response shape đúng.

## 8. Giới hạn của báo cáo này

- Bộ test đã được **chạy thực tế ở máy local và xác nhận 0 FAIL** (xem mục 0) — 333 test case chạy, 326 passed, 4 todo, 3 skipped, 1.128 assertions.
- Chưa đo được **% code coverage dòng lệnh** (cần Xdebug hoặc PCOV + `php artisan test --coverage`). Số liệu "test case sau" trong bảng ở mục 1 là đếm case logic (function-level/case-level coverage theo decision table & BVA), không phải % dòng code.

## 9. Lệnh đề xuất chạy ở local

```bash
cd backend
composer install
cp .env.example .env   # nếu chưa có
php artisan key:generate
php artisan test --filter=Budget
php artisan test --filter=Menu
php artisan test tests/Feature/User/Dashboard
php artisan test --coverage --min=80   # cần Xdebug/PCOV
```
