# Spendify Frontend

Ứng dụng Next.js độc lập sử dụng App Router, TypeScript, Tailwind CSS, TanStack Query, React Hook Form và Zod.

Frontend không sử dụng Inertia.js. Mọi dữ liệu và mutation đều đi qua REST API Laravel bằng API client tập trung.

## Cài đặt

```bash
cp .env.example .env.local
npm install
```

## Environment

```env
NEXT_PUBLIC_API_URL=http://localhost:8000/api/v1
NEXT_PUBLIC_BACKEND_URL=http://localhost:8000
LARAVEL_SESSION_COOKIE=laravel_session
```

## Chạy frontend

```bash
npm run dev
```

Ứng dụng mặc định chạy tại `http://localhost:3001` và backend chạy riêng tại `http://localhost:8000`. Cổng `3001` được cố định trong script `npm run dev` để tránh xung đột với ứng dụng khác đang dùng cổng `3000`.

## Kiểm tra

```bash
npm run lint
npm run typecheck
npm test
npm run build
```

## Authentication

Authentication dùng Laravel Sanctum SPA cookie:

1. Lấy CSRF cookie từ backend.
2. Gửi login/register request với `credentials: include`.
3. Kiểm tra phiên qua `GET /api/v1/auth/me`.
4. Không lưu access token nhạy cảm trong `localStorage`.
