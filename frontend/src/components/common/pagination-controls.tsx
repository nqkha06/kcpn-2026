import type { PaginationMeta } from "@/types/api";
import { ChevronLeft, ChevronRight } from "lucide-react";

interface PaginationControlsProps {
  meta: PaginationMeta;
  onPageChange: (page: number) => void;
  disabled?: boolean;
}

export function PaginationControls({ meta, onPageChange, disabled = false }: PaginationControlsProps) {
  if (meta.last_page <= 1) {
    return null;
  }

  return (
    <div className="flex flex-col items-center justify-between gap-3 border-t border-slate-100 px-4 py-4 text-sm sm:flex-row sm:px-6">
      <p className="text-slate-500">
        Trang <strong className="text-slate-700">{meta.current_page}</strong> / {meta.last_page} · {meta.total} kết quả
      </p>
      <div className="flex items-center gap-2">
        <button
          type="button"
          disabled={disabled || meta.current_page <= 1}
          onClick={() => onPageChange(meta.current_page - 1)}
          className="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
        >
          <ChevronLeft className="size-4" />
          Trước
        </button>
        <button
          type="button"
          disabled={disabled || meta.current_page >= meta.last_page}
          onClick={() => onPageChange(meta.current_page + 1)}
          className="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-2 font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
        >
          Sau
          <ChevronRight className="size-4" />
        </button>
      </div>
    </div>
  );
}
