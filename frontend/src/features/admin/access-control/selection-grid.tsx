import type { ReactNode } from "react";

export function SelectionGrid({
  items,
  selected,
  columns = "lg:grid-cols-3",
  emptyMessage,
  onToggle,
}: {
  items: Array<{ id: number; label: string }>;
  selected: number[];
  columns?: string;
  emptyMessage: string;
  onToggle: (id: number, checked: boolean) => void;
}) {
  if (items.length === 0) {
    return <p className="text-sm text-muted-foreground">{emptyMessage}</p>;
  }

  return (
    <div className={`mt-1 grid grid-cols-1 gap-3 sm:grid-cols-2 ${columns}`}>
      {items.map((item) => (
        <label
          key={item.id}
          className="flex cursor-pointer items-center gap-2 rounded-lg border p-3 text-sm"
        >
          <input
            type="checkbox"
            checked={selected.includes(item.id)}
            onChange={(event) => onToggle(item.id, event.target.checked)}
            className="size-4 rounded border-input accent-primary"
          />
          <span className="flex-1">{item.label}</span>
        </label>
      ))}
    </div>
  );
}

export function AdminFormActions({
  submitLabel,
  isPending,
  onCancel,
}: {
  submitLabel: string;
  isPending: boolean;
  onCancel: () => void;
}) {
  return (
    <div className="flex gap-2">
      <button
        type="submit"
        disabled={isPending}
        className="inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50"
      >
        {isPending ? "Saving..." : submitLabel}
      </button>
      <button
        type="button"
        disabled={isPending}
        onClick={onCancel}
        className="inline-flex h-9 items-center rounded-md border bg-background px-4 text-sm font-medium hover:bg-accent disabled:opacity-50"
      >
        Cancel
      </button>
    </div>
  );
}

export function AdminFormField({
  label,
  htmlFor,
  error,
  children,
}: {
  label: string;
  htmlFor?: string;
  error?: string;
  children: ReactNode;
}) {
  return (
    <div className="grid gap-2">
      <label htmlFor={htmlFor} className="text-sm font-medium">{label}</label>
      {children}
      {error ? <p className="text-sm text-destructive">{error}</p> : null}
    </div>
  );
}
