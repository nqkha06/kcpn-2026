import Link from "next/link";

export default function ForbiddenPage() {
  return (
    <main className="flex min-h-screen flex-col items-center justify-center gap-4 px-6 text-center">
      <p className="text-sm font-semibold text-destructive">403</p>
      <h1 className="text-3xl font-bold">Bạn không có quyền truy cập</h1>
      <Link className="text-sm font-medium underline underline-offset-4" href="/dashboard">
        Quay về dashboard
      </Link>
    </main>
  );
}
