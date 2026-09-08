# BÁO CÁO COVERAGE THEO BỘ TEST CASE / MODULE

## 1. Thông tin thực thi

| Hạng mục        | Kết quả                                       |
| --------------- | --------------------------------------------- |
| Ngày thực thi   | 26/08/2026 00:20:14 (UTC+07:00)               |
| Phạm vi source  | `backend/app`                                 |
| Test framework  | Pest v4.7.8 / PHPUnit 12                      |
| PHP             | 8.5.9                                         |
| Nhánh           | `main`                                        |
| Commit          | `00a01921ba016c0dc8be846bd9e1df5d897cfba8`    |
| Lệnh thu thập   | `./vendor/bin/pest --coverage-php=<file>`     |
| Lệnh đối chiếu  | `./vendor/bin/pest --coverage --only-covered` |
| Coverage driver | PCOV 1.0.12                                   |

## 2. Phương pháp tính

- Mỗi dòng là một **bộ test case của một module**, không phải một test function hoặc một dataset riêng lẻ.
- Statements đã cover là hợp các executable statements do toàn bộ test của module thực thi; statement trùng giữa nhiều test chỉ được tính một lần.
- Coverage module = statements đã cover / tổng executable statements thuộc tập source được bộ test của module thực thi.
- Không hiển thị cột hoặc danh sách source file theo yêu cầu.
- Module có coverage 0%, test skipped và test todo không được đưa vào bảng.
- Coverage giữa các module không cộng trực tiếp với nhau vì các module có thể cùng thực thi code dùng chung.

## 3. Tổng quan

| Chỉ số                                          |    Kết quả |
| ----------------------------------------------- | ---------: |
| Bộ test case/module được báo cáo                |     **19** |
| Test case passed nằm trong các module           |  **1.218** |
| Test case coverage 0% đã loại                   |      **8** |
| Skipped đã loại                                 |          9 |
| Todo đã loại                                    |          6 |
| Coverage module thấp nhất                       | **54,35%** |
| Coverage module cao nhất                        | **86,41%** |
| Tỷ lệ gộp có trọng số theo module               | **77,40%** |
| Coverage tập file `only-covered` của toàn suite |  **97,1%** |
| Coverage chính thức toàn source của Pest        |  **50,8%** |

## 4. Coverage theo bộ test case / module

| STT | Bộ test case / module | Test case passed | Statements đã cover |   Coverage | Kết quả |
| --: | --------------------- | ---------------: | ------------------: | ---------: | ------- |
|   1 | Admin / Appearance    |               42 |             170/226 | **75,22%** | Passed  |
|   2 | Admin / Budget        |              110 |             301/363 | **82,92%** | Passed  |
|   3 | Admin / Category      |               89 |             200/268 | **74,63%** | Passed  |
|   4 | Admin / Dashboard     |                4 |             174/250 | **69,60%** | Passed  |
|   5 | Admin / Menu          |              152 |             228/266 | **85,71%** | Passed  |
|   6 | Admin / Page          |               87 |             233/271 | **85,98%** | Passed  |
|   7 | Admin / Permission    |               75 |             167/204 | **81,86%** | Passed  |
|   8 | Admin / Role          |               87 |             192/229 | **83,84%** | Passed  |
|   9 | Admin / Transaction   |               95 |             337/390 | **86,41%** | Passed  |
|  10 | Admin / User          |               85 |             239/300 | **79,67%** | Passed  |
|  11 | Auth                  |               57 |             289/345 | **83,77%** | Passed  |
|  12 | Public                |               10 |             195/256 | **76,17%** | Passed  |
|  13 | Smoke                 |                1 |             146/220 | **66,36%** | Passed  |
|  14 | User / Budget         |               47 |             193/274 | **70,44%** | Passed  |
|  15 | User / Category       |               83 |             166/251 | **66,14%** | Passed  |
|  16 | User / Dashboard      |               11 |             175/322 | **54,35%** | Passed  |
|  17 | User / Settings       |               48 |             158/202 | **78,22%** | Passed  |
|  18 | User / Transaction    |               65 |             280/339 | **82,60%** | Passed  |
|  19 | User / Wallet         |               70 |             233/290 | **80,34%** | Passed  |

## 5. Kết luận

Báo cáo trình bày coverage theo **19 bộ test case/module**. Mỗi phần trăm phản ánh độ bao phủ hợp nhất của toàn bộ test trong module, phù hợp để đánh giá một nhóm chức năng thay vì từng test case chi tiết. Các module coverage 0%, test skipped và test todo đã được loại.
