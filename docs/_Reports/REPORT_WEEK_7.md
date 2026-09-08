# Laravel Executable Test Mapping Report

## Execution Summary

Nguồn sự thật là source hiện tại và từng leaf test do Pest/PHPUnit thực thi. Mỗi dataset row được tính là một executable test riêng.

| Metric | Result |
| --- | ---: |
| Total executable tests | 1,288 |
| Passed | 1,276 |
| Failed | 0 |
| Skipped | 9 |
| Todo | 3 |
| Assertions | 5,926 |

Lệnh xác nhận inventory và trạng thái:

```bash
XDEBUG_MODE=off php artisan test --compact --log-junit=/tmp/cashback-final-junit.xml
```

## Coverage

| Coverage | Actual result |
| --- | ---: |
| Statement Coverage (CS) | 2,534 / 2,534 = **100%** |
| Branch Coverage (CB) | **N/A — chưa có kết quả full-suite runtime cuối cùng** |

- Uncovered statements: **0**.
- Uncovered branches: **không kết luận**. Lượt Xdebug `--path-coverage` cuối đã được dừng theo chỉ đạo của người dùng, nên báo cáo không dùng static audit hoặc số liệu cũ để gắn nhãn CB 100%.
- CS lấy từ full-suite Clover hiện tại: `/tmp/cashback-final-lines.xml`.

## Module Mapping

CS trong bảng là kết quả của cùng full-suite Clover đã bao phủ toàn bộ executable statements trong `app/`. CB để `N/A` vì không có báo cáo Xdebug full-suite hoàn tất cho trạng thái source/test cuối.

| Module | Test Cases | EP | BVA | CS | CB |
| --- | ---: | ---: | ---: | ---: | ---: |
| Admin Appearance | 46 | 8 | 35 | 100% | N/A |
| Admin Budget | 117 | 62 | 38 | 100% | N/A |
| Admin Category | 96 | 48 | 33 | 100% | N/A |
| Admin Dashboard | 6 | 4 | 0 | 100% | N/A |
| Admin Menu | 159 | 75 | 60 | 100% | N/A |
| Admin Page | 97 | 43 | 34 | 100% | N/A |
| Admin Permission | 77 | 35 | 28 | 100% | N/A |
| Admin Role | 94 | 45 | 28 | 100% | N/A |
| Admin Transaction | 101 | 55 | 31 | 100% | N/A |
| Admin User | 89 | 54 | 23 | 100% | N/A |
| User Budget | 49 | 22 | 15 | 100% | N/A |
| User Category | 83 | 41 | 26 | 100% | N/A |
| User Dashboard | 11 | 7 | 0 | 100% | N/A |
| User Settings | 50 | 20 | 13 | 100% | N/A |
| User Transaction | 65 | 29 | 27 | 100% | N/A |
| User Wallet | 72 | 32 | 26 | 100% | N/A |
| Auth | 63 | 41 | 12 | 100% | N/A |
| Public | 13 | 9 | 0 | 100% | N/A |
| **Total** | **1,288** | **630** | **429** | **100%** | **N/A** |

Các executable tests không áp dụng EP/BVA được giữ đúng bản chất: Business Rule 193, Authorization 17, Branch Coverage 11 và N/A 8. Không có technique `Decision Table` hoặc `Other` trong workbook cuối.

## Excel Reconciliation

| Action | Count | Meaning |
| --- | ---: | --- |
| KEEP | 993 | TC cũ mapping 1:1 với executable test hiện tại |
| UPDATE | 5 | Dòng cũ chứa nhiều Laravel Test Reference được chuẩn hóa còn đúng một execution |
| MERGE | 2 | Dòng trùng cùng executable test bị loại khỏi Excel |
| DELETE | 0 | Không có TC cũ nào mất căn cứ ngoài hai dòng trùng được merge |
| ADD | 290 | 282 execution chưa có dòng và 8 execution tách từ các dòng nhiều reference |

- Total TC trước reconciliation: **1,000**.
- Total TC sau reconciliation: **1,288**.
- Workbook cuối có **1,288 TC ID duy nhất** và **1,288 Laravel Test Reference duy nhất**.
- Hai dòng trùng được merge:
  - `PST-USR-CAT-INDEX-CATEGORY-EP-L28` vào `USR-CAT-INDEX-COV-001`.
  - `PST-USR-DASH-SHOW-DASHBOARD-EP-L110` vào `USR-DASH-SHOW-COV-001`.

## Verification Notes

- Workbook giữ nguyên 19 sheet: 1 Statistics và 18 module sheets.
- Mỗi module giữ nguyên 15 cột và table style của template.
- Statistics lấy trực tiếp từ 1,288 dòng mapping cuối; cột `Automated + Manual` biểu diễn tổng executable tests của module.
- Workbook scan không phát hiện `#REF!`, `#DIV/0!`, `#VALUE!`, `#NAME?` hoặc `#N/A` formula errors.
- Không sửa source Laravel hoặc Pest/PHPUnit tests trong bước mapping này.
