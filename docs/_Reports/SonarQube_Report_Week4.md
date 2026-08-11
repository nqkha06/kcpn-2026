# BÁO CÁO PHÂN TÍCH MÃ NGUỒN SONARQUBE — TUẦN 4

## 1. Thông tin chung

| Hạng mục | Giá trị |
|---|---|
| Dự án | KCPM — Cashback |
| Tuần báo cáo | Tuần 4 |
| SonarQube Project Key | `cashback` |
| Project Version | `1.0` |
| Branch | `main` |
| SonarQube Server | [http://localhost:9000](http://localhost:9000) |
| Project Dashboard | [Cashback Dashboard](http://localhost:9000/dashboard?id=cashback) |
| Phiên bản SonarQube | Community Build `v26.6.0.123539` |
| Chế độ phân tích | MQR Mode |
| Scanner | SonarScanner CLI `8.0.1.6346` |
| Quality Gate | `Sonar way` — Built-in, mặc định |
| Thời điểm scan gần nhất | 04/08/2026 22:57:09, múi giờ Asia/Ho_Chi_Minh (UTC+07:00) |
| Trạng thái background task | Success |
| Thời gian xử lý background task | 2,986 giây |
| Analysis Task ID | `8f6eaacf-411f-4ac6-a93c-ca0b7d514290` |
| Thời điểm kiểm tra báo cáo | 11/08/2026 |
| Cấu hình scanner | [`sonarqube/sonar-project.properties`](../../sonarqube/sonar-project.properties) |

> **Lưu ý về tính thời điểm:** lần scan gần nhất được thực hiện ngày 04/08/2026, trong khi source hiện tại đã có thêm commit và các thay đổi test tuần 4. Vì vậy số liệu SonarQube trong báo cáo này phản ánh snapshot ngày 04/08/2026, chưa phản ánh toàn bộ working tree hiện tại.

## 2. Phạm vi phân tích

Theo cấu hình scanner, các thư mục source được phân tích gồm:

- `backend/app`, `backend/routes`, `backend/config`, `backend/database`.
- `frontend/src`.
- `codeceptjs`.

Các thư mục test được khai báo gồm:

- `backend/tests`.
- Các file `*.test.ts`, `*.test.tsx`, `*.spec.ts`, `*.spec.tsx` trong `frontend/src`.
- `codeceptjs/tests`.

Các thành phần sinh tự động, dependency, cache, build artifact, file môi trường, test source và coverage output được loại khỏi phân tích source theo [`sonar-project.properties`](../../sonarqube/sonar-project.properties).

## 3. Kết quả tổng quan

| Chỉ số | Kết quả | Đánh giá |
|---|---:|---|
| Quality Gate | **Passed** | Đạt điều kiện trên New Code |
| Lines of Code | 22.365 | Quy mô M |
| Tổng số dòng được xét duplication | 28.131 | Overall Code |
| Issue duy nhất đang Open/Confirmed | **236** | Cần xử lý |
| Security issues | 0 | Rating A |
| Reliability impacts | 32 | Rating C |
| Maintainability impacts | 235 | Rating A |
| Accepted issues | 0 | Không có issue được chấp nhận bỏ qua |
| Security Hotspots | 0 | Security Review rating A |
| Coverage | **0,0%** | Chưa import được báo cáo coverage |
| Lines to Cover | 3.459 | Chưa có dòng nào được ghi nhận đã cover |
| Duplicated Lines | 1.282 | Cần giảm |
| Duplicated Blocks | 60 | Cần rà soát |
| Duplicated Files | 31 | Cần rà soát |
| Duplicated Lines Density | **4,6%** | Cao hơn ngưỡng New Code 3% |
| Tổng remediation effort | 2 ngày 7 giờ | Ước lượng của SonarQube |

**Kết luận tổng quan:** Quality Gate đang **Passed**, tuy nhiên chất lượng tổng thể chưa thể coi là hoàn tất do Reliability ở mức C, có 236 issue, coverage bằng 0% và duplication bằng 4,6%.

## 4. Giải thích Quality Gate

Project sử dụng Quality Gate mặc định `Sonar way`. Các điều kiện được áp dụng trên **New Code** gồm:

- New Code không có issue.
- Toàn bộ Security Hotspot mới phải được review.
- Coverage trên New Code phải lớn hơn hoặc bằng 80%.
- Duplicated Lines trên New Code phải nhỏ hơn hoặc bằng 3%.

Quality Gate `Passed` không đồng nghĩa toàn bộ source hiện tại đã đạt các ngưỡng trên. Dashboard đang hiển thị số liệu **Overall Code** là coverage 0,0% và duplication 4,6%. Đây là lần phân tích đầu tiên và không có đủ dữ liệu New Code có ý nghĩa để Quality Gate phản ánh toàn bộ tồn đọng hiện tại.

## 5. Phân tích Issue

### 5.1. Số lượng issue duy nhất

SonarQube ghi nhận **236 issue duy nhất** ở trạng thái Open/Confirmed. Trong MQR Mode, một issue có thể tác động đồng thời đến nhiều software quality, vì vậy tổng các impact dưới đây có thể lớn hơn 236.

| Software Quality | Impact | Rating | Effort |
|---|---:|---:|---:|
| Security | 0 | A | 0 |
| Reliability | 32 | C | 2 giờ 40 phút |
| Maintainability | 235 | A | 2 ngày 7 giờ |

### 5.2. Mức độ nghiêm trọng

| Phạm vi | Blocker | High | Medium | Low | Info |
|---|---:|---:|---:|---:|---:|
| Reliability | 0 | 0 | 19 | 13 | 0 |
| Maintainability | 0 | 17 | 92 | 126 | 0 |
| Tổng impact theo severity | 0 | 17 | 111 | 139 | 0 |

> Tổng impact theo severity là 267, lớn hơn 236 issue duy nhất vì một số issue ảnh hưởng đồng thời Reliability và Maintainability.

### 5.3. Phân bố issue theo ngôn ngữ

| Ngôn ngữ | Issue | Tỷ lệ |
|---|---:|---:|
| TypeScript | 154 | 65,25% |
| PHP | 80 | 33,90% |
| JavaScript | 2 | 0,85% |
| CSS | 0 | 0,00% |
| **Tổng cộng** | **236** | **100%** |

TypeScript là khu vực có nhiều issue nhất, chiếm hơn 65% tổng số issue. Các lỗi frontend tập trung nhiều vào React props, accessibility, `tabIndex`, phần tử tương tác và cấu trúc JSX.

## 6. Reliability — 32 impact, Rating C

Reliability có **19 issue Medium** và **13 issue Low**. Hầu hết là vấn đề accessibility và hành vi tương tác của giao diện React.

### 6.1. Nhóm lỗi Reliability

| Quy tắc/Thông báo | Số lượng | Mức ưu tiên |
|---|---:|---|
| Không dùng giá trị `tabIndex` dương | 12 | Cao |
| `href` phải chứa địa chỉ điều hướng hợp lệ | 6 | Trung bình |
| Tránh dùng phần tử không tương tác thay cho control tương tác | 4 | Cao |
| Khoảng trắng không rõ ràng trước thẻ `input` | 3 | Trung bình |
| Heading phải có nội dung truy cập được bởi screen reader | 1 | Cao |
| Label phải liên kết với form control | 1 | Cao |
| Khoảng trắng không rõ ràng trước thẻ `select` | 1 | Trung bình |
| Khoảng trắng không rõ ràng trước thẻ `textarea` | 1 | Trung bình |
| Không gán interactive role cho phần tử không tương tác | 1 | Cao |
| Khoảng trắng không rõ ràng trước thẻ `span` | 1 | Trung bình |
| Khoảng trắng không rõ ràng sau thẻ `input` | 1 | Trung bình |
| **Tổng cộng** | **32** |  |

### 6.2. Các file có Reliability issue

| File | Issue |
|---|---:|
| [`frontend/src/app/(auth)/login/page.tsx`](../../frontend/src/app/(auth)/login/page.tsx) | 6 |
| [`frontend/src/app/(auth)/register/page.tsx`](../../frontend/src/app/(auth)/register/page.tsx) | 6 |
| [`frontend/src/features/admin/content/appearance-view.tsx`](../../frontend/src/features/admin/content/appearance-view.tsx) | 5 |
| [`frontend/src/features/wallets/wallets-view.tsx`](../../frontend/src/features/wallets/wallets-view.tsx) | 4 |
| [`frontend/src/components/layouts/finance-layout.tsx`](../../frontend/src/components/layouts/finance-layout.tsx) | 3 |
| [`frontend/src/components/layouts/public-layout.tsx`](../../frontend/src/components/layouts/public-layout.tsx) | 3 |
| [`frontend/src/components/ui/card.tsx`](../../frontend/src/components/ui/card.tsx) | 1 |
| [`frontend/src/components/ui/label.tsx`](../../frontend/src/components/ui/label.tsx) | 1 |
| [`frontend/src/features/budgets/budget-dialog.tsx`](../../frontend/src/features/budgets/budget-dialog.tsx) | 1 |
| [`frontend/src/features/categories/category-dialog.tsx`](../../frontend/src/features/categories/category-dialog.tsx) | 1 |
| [`frontend/src/features/transactions/transaction-dialog.tsx`](../../frontend/src/features/transactions/transaction-dialog.tsx) | 1 |
| **Tổng cộng** | **32** |

### 6.3. Đề xuất xử lý Reliability

- Thay `tabIndex` dương bằng `0`, `-1` hoặc sử dụng thứ tự DOM tự nhiên.
- Thay thẻ `<a>` không có URL thật bằng `<button>`; dùng `href` hợp lệ cho liên kết điều hướng.
- Ưu tiên `<button>`, `<input>`, `<select>` và các phần tử HTML semantic thay vì gắn role tương tác lên `<div>`/`<span>`.
- Bảo đảm mọi form label có `htmlFor` liên kết đúng với `id` của control.
- Bổ sung nội dung hoặc accessible name cho heading/control.
- Làm rõ khoảng trắng trong JSX bằng text node hoặc biểu thức `{' '}` khi cần.

## 7. Maintainability — 235 impact, Rating A

Maintainability có **17 issue High**, **92 issue Medium** và **126 issue Low**. Rating vẫn là A vì remediation cost tương đối thấp so với quy mô source, nhưng số lượng tồn đọng lớn và cần được giảm theo từng nhóm rule.

### 7.1. Các rule Maintainability xuất hiện nhiều nhất

| STT | Rule | Ngôn ngữ | Số lượng |
|---:|---|---|---:|
| 1 | React props nên ở trạng thái read-only | TypeScript | 67 |
| 2 | Xóa function parameter không được sử dụng | PHP | 42 |
| 3 | `tabIndex` chỉ nên là `0` hoặc `-1` | TypeScript | 12 |
| 4 | Không lồng nhiều ternary operator | TypeScript | 10 |
| 5 | Ưu tiên HTML tag semantic thay cho ARIA role | TypeScript | 8 |
| 6 | Tránh cast hoặc non-null assertion dư thừa | TypeScript | 8 |
| 7 | Xóa các đoạn source đã bị comment-out | PHP | 7 |
| 8 | Không lặp lại string literal | PHP | 7 |
| 9 | Làm rõ khoảng trắng giữa inline element | TypeScript | 7 |
| 10 | Không dùng anchor như button | TypeScript | 6 |
| 11 | Không sử dụng API đã deprecated | TypeScript | 6 |
| 12 | Dùng `globalThis` thay cho `window`, `self` hoặc `global` | TypeScript | 6 |
| 13 | Giảm Cognitive Complexity của function | PHP | 5 |
| 14 | Tránh điều kiện phủ định khi có nhánh `else` | TypeScript | 5 |
| 15 | Không khai báo biến chỉ để return/throw ngay sau đó | PHP | 4 |
|  | Các rule khác | PHP/TypeScript/JavaScript | 35 |
| **Tổng cộng** |  |  | **235** |

### 7.2. Đề xuất xử lý Maintainability

- Chuyển interface/type của React props sang `Readonly<...>` hoặc khai báo thuộc tính `readonly`.
- Xóa parameter PHP không sử dụng; nếu bắt buộc bởi interface/override thì xác minh và đánh dấu ngoại lệ hợp lý.
- Tách nested ternary thành biến mô tả, `if/else` hoặc helper function.
- Tách các function có Cognitive Complexity cao thành các service/helper nhỏ theo trách nhiệm.
- Gom string literal lặp lại thành constant hoặc enum dùng chung.
- Xóa source đã comment-out vì lịch sử đã được quản lý bởi Git.
- Thay API deprecated và chuẩn hóa truy cập global bằng `globalThis`.

## 8. Security và Security Hotspots

| Chỉ số | Kết quả |
|---|---:|
| Security issue | 0 |
| Security rating | A |
| Security Hotspot | 0 |
| Hotspot reviewed | Không có hotspot cần review |
| Security Review rating | A |

Không phát hiện security issue hoặc security hotspot trong phạm vi source của lần scan. Tuy nhiên, đây là kết quả static analysis theo các Quality Profile hiện hành, không thay thế kiểm thử xâm nhập, dependency audit hoặc kiểm tra cấu hình production.

## 9. Coverage — 0,0%

SonarQube ghi nhận **0,0% coverage trên 3.459 lines to cover**.

### Nguyên nhân

- Background task cảnh báo không đọc được `/usr/src/backend/coverage.xml` do file không tồn tại.
- File `backend/coverage.xml` hiện không có trong workspace.
- File `frontend/coverage/lcov.info` hiện cũng không có trong workspace.
- Scanner không tự chạy test; scanner chỉ import các báo cáo coverage đã được tạo trước khi scan.

Coverage 0,0% không có nghĩa dự án không có test. Báo cáo [Unit Test tuần 4](./UnitTest_Report_Week4.md) cho thấy test suite backend hiện có 434 test pass, nhưng SonarQube chưa nhận được file coverage để ánh xạ test execution vào source code.

### Cách khắc phục

1. Cài/bật Xdebug hoặc PCOV cho PHP và tạo Clover report:

   ```bash
   cd backend
   XDEBUG_MODE=coverage php artisan test --compact --coverage-clover=coverage.xml
   cd ..
   ```

2. Cài coverage provider cho Vitest nếu project chưa có, sau đó tạo LCOV report:

   ```bash
   cd frontend
   npm run test -- --coverage --coverage.reporter=text --coverage.reporter=lcov
   cd ..
   ```

3. Xác nhận hai file tồn tại:

   ```text
   backend/coverage.xml
   frontend/coverage/lcov.info
   ```

4. Chạy lại scanner và kiểm tra Coverage trên dashboard.

## 10. Duplications — 4,6%

SonarQube ghi nhận:

- 1.282 duplicated lines.
- 60 duplicated blocks.
- 31 duplicated files.
- Mật độ duplicated lines là 4,6% trên 28.131 dòng.

### Các file có tỷ lệ duplication cao nhất

| STT | File | Duplicated Lines | Density |
|---:|---|---:|---:|
| 1 | [`backend/app/Http/Controllers/User/DashboardController.php`](../../backend/app/Http/Controllers/User/DashboardController.php) | 61 | 84,7% |
| 2 | [`backend/app/Http/Controllers/User/HomeController.php`](../../backend/app/Http/Controllers/User/HomeController.php) | 61 | 84,7% |
| 3 | [`backend/app/Services/Admin/AdminDashboardService.php`](../../backend/app/Services/Admin/AdminDashboardService.php) | 88 | 66,7% |
| 4 | [`backend/app/Http/Requests/Api/V1/Admin/PermissionIndexRequest.php`](../../backend/app/Http/Requests/Api/V1/Admin/PermissionIndexRequest.php) | 25 | 64,1% |
| 5 | [`backend/app/Http/Requests/Api/V1/Admin/RoleIndexRequest.php`](../../backend/app/Http/Requests/Api/V1/Admin/RoleIndexRequest.php) | 25 | 64,1% |
| 6 | [`backend/app/Http/Controllers/Admin/DashboardController.php`](../../backend/app/Http/Controllers/Admin/DashboardController.php) | 85 | 63,9% |
| 7 | [`backend/database/seeders/MenuSeeder.php`](../../backend/database/seeders/MenuSeeder.php) | 100 | 62,5% |
| 8 | [`backend/database/seeders/PageSeeder.php`](../../backend/database/seeders/PageSeeder.php) | 36 | 47,4% |
| 9 | [`backend/app/Support/Authorization/PermissionCatalog.php`](../../backend/app/Support/Authorization/PermissionCatalog.php) | 94 | 47,0% |
| 10 | [`backend/app/Http/Requests/User/StoreExpenseTransactionRequest.php`](../../backend/app/Http/Requests/User/StoreExpenseTransactionRequest.php) | 52 | 46,8% |
| 11 | [`backend/app/Http/Requests/Admin/ExpenseTransactionRequest.php`](../../backend/app/Http/Requests/Admin/ExpenseTransactionRequest.php) | 55 | 45,8% |
| 12 | [`backend/app/Http/Requests/Api/V1/Admin/BudgetIndexRequest.php`](../../backend/app/Http/Requests/Api/V1/Admin/BudgetIndexRequest.php) | 20 | 42,6% |
| 13 | [`backend/app/Http/Requests/Api/V1/Admin/TransactionIndexRequest.php`](../../backend/app/Http/Requests/Api/V1/Admin/TransactionIndexRequest.php) | 20 | 40,8% |
| 14 | [`backend/app/Http/Requests/Api/V1/User/TransactionIndexRequest.php`](../../backend/app/Http/Requests/Api/V1/User/TransactionIndexRequest.php) | 25 | 40,3% |
| 15 | [`backend/app/Http/Requests/Api/V1/Admin/MenuIndexRequest.php`](../../backend/app/Http/Requests/Api/V1/Admin/MenuIndexRequest.php) | 19 | 39,6% |

### Hướng xử lý duplication

- So sánh `DashboardController` và `HomeController`; hợp nhất hoặc chuyển logic chung sang service dùng lại.
- Rà soát các `*IndexRequest`; trích xuất validation chung về sort, pagination, date filter và search vào trait/base request phù hợp với kiến trúc hiện tại.
- Tái sử dụng transaction rules giữa Admin/User request thay vì duy trì hai bản gần giống nhau.
- Chuẩn hóa dashboard query/tổng hợp trong service thay vì lặp ở controller và service khác nhau.
- Với seeder, cân nhắc builder/helper cho dữ liệu đa ngôn ngữ hoặc cấu trúc lặp.
- Chỉ refactor khi bảo toàn hành vi và có test regression cho phần được thay đổi.

## 11. Cảnh báo của lần scan

Background task hoàn thành với trạng thái **Success**, nhưng SonarQube ghi nhận hai cảnh báo:

1. Không đọc được `/usr/src/backend/coverage.xml`; không có dữ liệu coverage backend được import.
2. Thiếu blame information cho **48 file**, có thể làm các tính năng liên quan SCM/New Code hoạt động không đầy đủ.

Để khắc phục cảnh báo blame:

- Bảo đảm scanner nhận được repository `.git` đầy đủ, không phải shallow/incomplete checkout.
- Không chạy scanner trên source copy đã loại bỏ metadata Git.
- Kiểm tra log scanner để xác định chính xác 48 file thiếu blame.
- Commit các thay đổi cần phân tích trước khi tạo mốc scan chính thức.

## 12. Quality Profiles đang áp dụng

Tất cả ngôn ngữ sử dụng Quality Profile mặc định `Sonar way`:

| Ngôn ngữ | Quality Profile | Active Rules |
|---|---|---:|
| CSS | Sonar way | 27 |
| JavaScript | Sonar way | 378 |
| PHP | Sonar way | 176 |
| TypeScript | Sonar way | 391 |

## 13. Kế hoạch xử lý đề xuất

### Ưu tiên P0 — Hoàn thiện dữ liệu scan

- Tạo `backend/coverage.xml` và `frontend/coverage/lcov.info`.
- Bảo đảm scanner truy cập được `.git` để có blame information.
- Commit source/test tuần 4 và chạy lại scan để báo cáo phản ánh đúng phiên bản hiện tại.

### Ưu tiên P1 — Reliability và accessibility

- Xử lý 19 Reliability issue mức Medium trước.
- Ưu tiên trang login/register, các dialog tài chính và component UI dùng chung.
- Sau khi sửa, chạy lint, frontend test và kiểm tra tương tác bàn phím/screen reader cơ bản.

### Ưu tiên P1 — Maintainability High

- Rà soát 17 Maintainability issue mức High.
- Giảm Cognitive Complexity, nested ternary và các đoạn logic khó bảo trì.

### Ưu tiên P2 — Maintainability theo nhóm rule

- Xử lý theo batch: 67 React props read-only, 42 PHP unused parameters, string literal lặp và code comment-out.
- Mỗi batch nên có commit riêng để dễ review và rollback.

### Ưu tiên P2 — Duplication

- Xử lý các file có density trên 40% trước.
- Mục tiêu đưa Overall Code xuống dưới 3% nếu khả thi và giữ New Code không vượt quá 3%.

## 14. Kết luận

Lần phân tích SonarQube hiện tại đã hoàn thành thành công và Quality Gate báo **Passed**, đồng thời không phát hiện Security issue hoặc Security Hotspot. Tuy nhiên, kết quả còn ba hạn chế quan trọng:

- Reliability chỉ đạt hạng C với 32 impact.
- Coverage bằng 0,0% do thiếu báo cáo coverage.
- Duplication bằng 4,6%, gồm 1.282 dòng trùng trong 31 file.

Ngoài ra, scan ngày 04/08/2026 đã cũ hơn source và test tuần 4. Nhóm nên tạo coverage, hoàn thiện metadata Git, sửa nhóm Reliability/High issue, sau đó chạy lại SonarQube để có báo cáo tuần 4 chính thức và có thể truy vết theo commit.
