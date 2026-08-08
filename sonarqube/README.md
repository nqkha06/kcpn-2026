# SonarQube local setup

Hướng dẫn setup SonarQube

- `backend/`: Laravel/PHP.
- `frontend/`: Next.js/TypeScript.
- `codeceptjs/`: end-to-end tests.

## Yêu cầu

- Docker Engine 20.10+ hoặc Docker Desktop phiên bản mới.
- Docker Compose v2 (`docker compose`).
- Tối thiểu khoảng 4 GB RAM trống cho Docker.
- Port `9000` chưa được ứng dụng khác sử dụng, hoặc cấu hình port khác theo hướng dẫn bên dưới.

Tất cả lệnh trong tài liệu được chạy từ thư mục gốc repository:

```bash
cd kcpn-2026
```

## Quick start

### 1. Khởi động SonarQube

```bash
docker compose -f sonarqube/compose.yml up -d database sonarqube
```

Theo dõi quá trình khởi động:

```bash
docker compose -f sonarqube/compose.yml ps
docker compose -f sonarqube/compose.yml logs -f sonarqube
```

Lần đầu khởi động có thể mất vài phút. SonarQube sẵn sàng khi log hiển thị `SonarQube is operational`. Nhấn `Ctrl+C` để thoát màn hình log; các container vẫn tiếp tục chạy nền.

Mở <http://localhost:9000> và đăng nhập lần đầu bằng:

```text
Username: admin
Password: admin
```

SonarQube sẽ yêu cầu đổi mật khẩu ngay sau lần đăng nhập đầu tiên.

### 2. Tạo project và token

Trong giao diện SonarQube:

1. Chọn **Projects → Create Project → Local**.
2. Đặt **Project key** là `cashback` để khớp với `sonar-project.properties`.
3. Tạo **Project Analysis Token** và sao chép token vừa sinh.

Nếu project đã tồn tại, có thể tạo token tại **My Account → Security**. Không commit hoặc gửi token lên chat, issue hay tài liệu nội bộ.

Export token trong terminal đang dùng để scan:

```bash
export SONAR_TOKEN='sqp_xxxxxxxxxxxxxxxxxxxx'
```

### 3. Chạy phân tích

```bash
docker compose -f sonarqube/compose.yml --profile scanner run --rm scanner
```

Khi terminal hiển thị `EXECUTION SUCCESS`, mở project `Cashback` trên <http://localhost:9000> để xem Quality Gate, bugs, vulnerabilities, security hotspots, code smells, duplication và coverage.

## Tạo báo cáo coverage

SonarQube không tự chạy test. Scanner chỉ đọc các báo cáo đã được tạo trước đó tại:

| Thành phần     | File SonarQube đọc            |
| -------------- | ----------------------------- |
| Laravel/Pest   | `backend/coverage.xml`        |
| Next.js/Vitest | `frontend/coverage/lcov.info` |

Không có file coverage thì quá trình phân tích source code vẫn chạy, nhưng coverage của thành phần tương ứng sẽ không được cập nhật.

### Backend (Pest)

Máy local cần bật Xdebug hoặc PCOV ở chế độ coverage, sau đó chạy:

```bash
cd backend
XDEBUG_MODE=coverage php artisan test --compact --coverage-clover=coverage.xml
cd ..
```

Nếu dùng PCOV, không cần đặt `XDEBUG_MODE`:

```bash
cd backend
php artisan test --compact --coverage-clover=coverage.xml
cd ..
```

### Frontend (Vitest)

Repository hiện chưa cài coverage provider cho Vitest. Maintainer chỉ cần cài một lần và commit thay đổi của `package.json` cùng `package-lock.json`:

```bash
cd frontend
npm install --save-dev @vitest/coverage-v8@3.2.7
cd ..
```

Sau khi provider đã có trong project, mỗi thành viên tạo báo cáo LCOV bằng:

```bash
cd frontend
npm run test -- --coverage --coverage.reporter=text --coverage.reporter=lcov
cd ..
```

Sau khi tạo coverage, chạy lại scanner:

```bash
docker compose -f sonarqube/compose.yml --profile scanner run --rm scanner
```

## Cấu hình tùy chọn

Giá trị mặc định dùng cho môi trường local:

| Biến                | Mặc định                | Mục đích                              |
| ------------------- | ----------------------- | ------------------------------------- |
| `SONARQUBE_PORT`    | `9000`                  | Port SonarQube trên máy host          |
| `SONAR_DB_NAME`     | `sonar`                 | Tên PostgreSQL database               |
| `SONAR_DB_USER`     | `sonar`                 | PostgreSQL user                       |
| `SONAR_DB_PASSWORD` | `sonar_local_password`  | PostgreSQL password                   |
| `SONAR_HOST_URL`    | `http://sonarqube:9000` | URL scanner dùng trong Docker network |
| `SONAR_TOKEN`       | rỗng                    | Token xác thực khi phân tích          |

Có thể tạo file `sonarqube/.env` để override cấu hình local. File `.env` đã được Git bỏ qua và không được commit:

```dotenv
SONARQUBE_PORT=9001
SONAR_DB_NAME=sonar
SONAR_DB_USER=sonar
SONAR_DB_PASSWORD=replace_with_a_local_password
```

Sau khi đổi port, truy cập `http://localhost:9001`. Giữ nguyên `SONAR_HOST_URL=http://sonarqube:9000` vì scanner kết nối tới SonarQube qua Docker network, không qua port của host.

## Lệnh vận hành

Xem trạng thái:

```bash
docker compose -f sonarqube/compose.yml ps
```

Xem log:

```bash
docker compose -f sonarqube/compose.yml logs -f sonarqube
```

Dừng và giữ lại dữ liệu:

```bash
docker compose -f sonarqube/compose.yml down
```

Khởi động lại:

```bash
docker compose -f sonarqube/compose.yml up -d database sonarqube
```

Xóa toàn bộ dữ liệu SonarQube local và khởi tạo lại từ đầu:

```bash
docker compose -f sonarqube/compose.yml down --volumes
```

> **Cảnh báo:** `down --volumes` xóa database, users, projects, tokens, lịch sử phân tích, plugins và scanner cache của môi trường local này. Dữ liệu không thể khôi phục nếu không có backup.

## Tài liệu tham khảo

- [Cài SonarQube Community Build bằng Docker](https://docs.sonarsource.com/sonarqube-community-build/server-installation/from-docker-image/installation-overview)
- [Yêu cầu hệ thống Linux cho SonarQube](https://docs.sonarsource.com/sonarqube-community-build/server-installation/pre-installation/linux)
- [SonarScanner CLI](https://docs.sonarsource.com/sonarqube-community-build/analyzing-source-code/scanners/sonarscanner)
