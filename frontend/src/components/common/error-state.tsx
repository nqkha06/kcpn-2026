import { AlertCircle, RotateCcw } from "lucide-react";

interface ErrorStateProps {
  message?: string;
  onRetry?: () => void;
}

export function ErrorState({
  message = "Không thể tải dữ liệu. Vui lòng thử lại.",
  onRetry,
}: ErrorStateProps) {
  return (
    <div className="flex min-h-64 flex-col items-center justify-center gap-4 rounded-2xl border border-danger-500/20 bg-danger-50 p-8 text-center">
      <span className="flex size-12 items-center justify-center rounded-full bg-white text-danger-600 shadow-sm">
        <AlertCircle className="size-6" aria-hidden="true" />
      </span>
      <div className="space-y-1">
        <p className="font-semibold text-slate-900">Đã xảy ra lỗi</p>
        <p className="text-sm text-slate-600">{message}</p>
      </div>
      {onRetry ? (
        <button
          type="button"
          onClick={onRetry}
          className="inline-flex items-center gap-2 rounded-xl bg-danger-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-red-700"
        >
          <RotateCcw className="size-4" aria-hidden="true" />
          Thử lại
        </button>
      ) : null}
    </div>
  );
}
