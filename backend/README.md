# Spendify Backend

Laravel 12 backend cung cấp REST API có version tại `/api/v1` và sử dụng database hiện tại.

## Yêu cầu

- PHP 8.4+
- Composer 2
- Database tương thích với cấu hình dự án hiện tại

## Cài đặt

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
```

## Chạy backend

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

API base URL local: `http://localhost:8000/api/v1`.

## Cấu hình frontend và Sanctum

```env
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3001
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3001
SESSION_DOMAIN=localhost
SESSION_COOKIE=laravel_session
```

Frontend cần lấy CSRF cookie trước các request thay đổi trạng thái và gửi cookie bằng `credentials: include`.

## Kiểm thử

```bash
php artisan test --compact
```

Backend chỉ đăng ký REST API và health endpoint. Giao diện được triển khai độc lập trong thư mục `frontend/`.
