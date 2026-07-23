"use client";

import { useEffect } from "react";

interface ConfirmDeleteDialogProps {
  open: boolean;
  title: string;
  description: string;
  isPending?: boolean;
  onCancel: () => void;
  onConfirm: () => void;
}

export function ConfirmDeleteDialog({
  open,
  title,
  description,
  isPending = false,
  onCancel,
  onConfirm,
}: ConfirmDeleteDialogProps) {
  useEffect(() => {
    if (!open) return;

    function closeOnEscape(event: KeyboardEvent): void {
      if (event.key === "Escape" && !isPending) onCancel();
    }

    document.addEventListener("keydown", closeOnEscape);
    return () => document.removeEventListener("keydown", closeOnEscape);
  }, [isPending, onCancel, open]);

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-[70] flex items-center justify-center p-4" role="dialog" aria-modal="true">
      <button
        type="button"
        aria-label="Close confirmation"
        onClick={onCancel}
        disabled={isPending}
        className="absolute inset-0 bg-black/50"
      />
      <div className="relative w-full max-w-lg rounded-lg border bg-background p-6 shadow-lg">
        <h2 className="text-lg font-semibold">{title}</h2>
        <p className="mt-2 text-sm text-muted-foreground">{description}</p>
        <div className="mt-6 flex justify-end gap-2">
          <button
            type="button"
            disabled={isPending}
            onClick={onCancel}
            className="inline-flex h-9 items-center rounded-md border bg-background px-4 text-sm font-medium hover:bg-accent disabled:opacity-50"
          >
            Cancel
          </button>
          <button
            type="button"
            disabled={isPending}
            onClick={onConfirm}
            className="inline-flex h-9 items-center rounded-md bg-destructive px-4 text-sm font-medium text-white hover:bg-destructive/90 disabled:opacity-50"
          >
            {isPending ? "Deleting..." : "Delete"}
          </button>
        </div>
      </div>
    </div>
  );
}
