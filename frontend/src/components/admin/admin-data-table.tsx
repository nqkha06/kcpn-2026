"use client";

import { cn } from "@/lib/utils";
import type { PaginationMeta } from "@/types/api";
import {
  ArrowDown,
  ArrowUp,
  ArrowUpDown,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  Columns3,
  Filter,
  Pencil,
  Search,
  Trash2,
} from "lucide-react";
import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { useState, type FormEvent, type ReactNode } from "react";

export interface AdminTableColumn<T> {
  id: string;
  label: string;
  sortable?: boolean;
  render?: (item: T) => ReactNode;
}

export interface AdminTableFilter {
  key: string;
  label: string;
  type: "input" | "select" | "date";
  placeholder?: string;
  options?: Array<{ value: string; label: string }>;
}

interface AdminDataTableProps<T extends { id: number }> {
  data: T[];
  columns: AdminTableColumn<T>[];
  meta: PaginationMeta;
  filters?: AdminTableFilter[];
  isFetching?: boolean;
  onEdit: (item: T) => void;
  onDelete: (item: T) => void;
}

function displayValue(value: unknown, column: string): string {
  if (value === null || value === undefined || value === "") return "—";

  if (column.endsWith("_at") && typeof value === "string") {
    return new Intl.DateTimeFormat("en-US", { dateStyle: "medium" }).format(new Date(value));
  }

  return String(value);
}

export function AdminDataTable<T extends { id: number }>({
  data,
  columns,
  meta,
  filters = [],
  isFetching = false,
  onEdit,
  onDelete,
}: AdminDataTableProps<T>) {
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [search, setSearch] = useState(searchParams.get("search") ?? "");
  const [showFilters, setShowFilters] = useState(
    filters.some((filter) => Boolean(searchParams.get(filter.key))),
  );
  const [showColumns, setShowColumns] = useState(false);
  const [visibleColumns, setVisibleColumns] = useState(() => columns.map((column) => column.id));

  function applyQuery(updates: Record<string, string | number | null>, resetPage = true): void {
    const query = new URLSearchParams(searchParams.toString());

    Object.entries(updates).forEach(([key, value]) => {
      if (value === null || value === "") query.delete(key);
      else query.set(key, String(value));
    });

    if (resetPage && !("page" in updates)) query.set("page", "1");
    router.replace(`${pathname}?${query.toString()}`, { scroll: false });
  }

  function submitSearch(event: FormEvent<HTMLFormElement>): void {
    event.preventDefault();
    applyQuery({ search: search.trim() || null });
  }

  function changeSort(column: string): void {
    const currentSort = searchParams.get("sort");
    const currentDirection = searchParams.get("direction") ?? "asc";
    applyQuery({
      sort: column,
      direction: currentSort === column && currentDirection === "asc" ? "desc" : "asc",
    });
  }

  function sortIcon(column: string) {
    if (searchParams.get("sort") !== column) return <ArrowUpDown className="size-4" />;
    return searchParams.get("direction") === "desc" ? (
      <ArrowDown className="size-4" />
    ) : (
      <ArrowUp className="size-4" />
    );
  }

  const firstItem = meta.total === 0 ? 0 : (meta.current_page - 1) * meta.per_page + 1;
  const lastItem = Math.min(meta.current_page * meta.per_page, meta.total);

  return (
    <div className="rounded-xl border bg-card text-card-foreground shadow-sm">
      <div className="space-y-4 p-6">
        <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
          <form onSubmit={submitSearch} className="flex w-full max-w-sm items-center gap-2">
            <div className="relative flex-1">
              <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
              <input
                type="search"
                value={search}
                onChange={(event) => setSearch(event.target.value)}
                placeholder="Search..."
                className="h-9 w-full rounded-md border bg-background pr-3 pl-9 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/30"
              />
            </div>
            <button
              type="submit"
              className="inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90"
            >
              Search
            </button>
          </form>

          <div className="flex items-center gap-2">
            {filters.length > 0 ? (
              <button
                type="button"
                onClick={() => setShowFilters((current) => !current)}
                className={cn(
                  "inline-flex h-9 items-center gap-2 rounded-md border bg-background px-3 text-sm font-medium hover:bg-accent",
                  showFilters && "bg-accent",
                )}
              >
                <Filter className="size-4" />
                Filters
              </button>
            ) : null}

            <div className="relative">
              <button
                type="button"
                onClick={() => setShowColumns((current) => !current)}
                className="inline-flex h-9 items-center gap-2 rounded-md border bg-background px-3 text-sm font-medium hover:bg-accent"
              >
                <Columns3 className="size-4" />
                Columns
              </button>
              {showColumns ? (
                <div className="absolute top-11 right-0 z-20 w-48 rounded-md border bg-popover p-1 shadow-md">
                  {columns.map((column) => (
                    <label
                      key={column.id}
                      className="flex cursor-pointer items-center gap-2 rounded-sm px-2 py-1.5 text-sm hover:bg-accent"
                    >
                      <input
                        type="checkbox"
                        checked={visibleColumns.includes(column.id)}
                        onChange={(event) =>
                          setVisibleColumns((current) =>
                            event.target.checked
                              ? [...current, column.id]
                              : current.filter((id) => id !== column.id),
                          )
                        }
                      />
                      {column.label}
                    </label>
                  ))}
                </div>
              ) : null}
            </div>
          </div>
        </div>

        {showFilters ? (
          <div className="grid gap-4 rounded-lg border p-4 sm:grid-cols-2 lg:grid-cols-4">
            {filters.map((filter) => (
              <label key={filter.key} className="grid gap-2 text-sm font-medium">
                {filter.label}
                {filter.type === "select" ? (
                  <select
                    value={searchParams.get(filter.key) ?? ""}
                    onChange={(event) => applyQuery({ [filter.key]: event.target.value || null })}
                    className="h-9 rounded-md border bg-background px-3 text-sm font-normal"
                  >
                    <option value="">{filter.placeholder ?? `All ${filter.label}`}</option>
                    {filter.options?.map((option) => (
                      <option key={option.value} value={option.value}>
                        {option.label}
                      </option>
                    ))}
                  </select>
                ) : (
                  <input
                    type={filter.type === "date" ? "date" : "text"}
                    value={searchParams.get(filter.key) ?? ""}
                    placeholder={filter.placeholder}
                    onChange={(event) => applyQuery({ [filter.key]: event.target.value || null })}
                    className="h-9 rounded-md border bg-background px-3 text-sm font-normal"
                  />
                )}
              </label>
            ))}
          </div>
        ) : null}

        <div className={cn("overflow-x-auto rounded-md border", isFetching && "opacity-70")}>
          <table className="w-full text-sm">
            <thead className="border-b bg-muted/40 text-left">
              <tr>
                {columns
                  .filter((column) => visibleColumns.includes(column.id))
                  .map((column) => (
                    <th key={column.id} className="h-11 px-4 font-medium text-muted-foreground">
                      {column.sortable ? (
                        <button
                          type="button"
                          onClick={() => changeSort(column.id)}
                          className="-ml-2 inline-flex h-8 items-center gap-2 rounded-md px-2 hover:bg-accent"
                        >
                          {column.label}
                          {sortIcon(column.id)}
                        </button>
                      ) : (
                        column.label
                      )}
                    </th>
                  ))}
                <th className="h-11 px-4 text-right font-medium text-muted-foreground">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y">
              {data.length === 0 ? (
                <tr>
                  <td
                    colSpan={visibleColumns.length + 1}
                    className="h-24 px-4 text-center text-muted-foreground"
                  >
                    No data found.
                  </td>
                </tr>
              ) : (
                data.map((item) => (
                  <tr key={item.id} className="transition-colors hover:bg-muted/40">
                    {columns
                      .filter((column) => visibleColumns.includes(column.id))
                      .map((column) => (
                        <td
                          key={column.id}
                          className={cn("px-4 py-3", column.id === "name" && "font-medium")}
                        >
                          {column.render
                            ? column.render(item)
                            : displayValue((item as Record<string, unknown>)[column.id], column.id)}
                        </td>
                      ))}
                    <td className="px-4 py-3">
                      <div className="flex justify-end gap-1">
                        <button
                          type="button"
                          onClick={() => onEdit(item)}
                          aria-label="Edit"
                          className="flex size-8 items-center justify-center rounded-md hover:bg-accent"
                        >
                          <Pencil className="size-4" />
                        </button>
                        <button
                          type="button"
                          onClick={() => onDelete(item)}
                          aria-label="Delete"
                          className="flex size-8 items-center justify-center rounded-md hover:bg-accent hover:text-destructive"
                        >
                          <Trash2 className="size-4" />
                        </button>
                      </div>
                    </td>
                  </tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        <div className="flex flex-col items-center justify-between gap-4 px-2 sm:flex-row">
          <p className="text-sm text-muted-foreground">
            Showing {firstItem} to {lastItem} of {meta.total}
          </p>
          <div className="flex items-center gap-4">
            <select
              aria-label="Rows per page"
              value={String(meta.per_page)}
              onChange={(event) => applyQuery({ per_page: event.target.value, page: 1 }, false)}
              className="h-8 w-[72px] rounded-md border bg-background px-2 text-sm"
            >
              {[10, 15, 20, 50, 100].map((size) => (
                <option key={size} value={size}>{size}</option>
              ))}
            </select>
            <div className="flex items-center gap-1">
              <PageButton
                label="First page"
                disabled={meta.current_page === 1}
                onClick={() => applyQuery({ page: 1 }, false)}
                className="hidden sm:flex"
              >
                <ChevronsLeft className="size-4" />
              </PageButton>
              <PageButton
                label="Previous page"
                disabled={meta.current_page === 1}
                onClick={() => applyQuery({ page: meta.current_page - 1 }, false)}
              >
                <ChevronLeft className="size-4" />
              </PageButton>
              <span className="px-2 text-sm tabular-nums">
                {meta.current_page}/{meta.last_page}
              </span>
              <PageButton
                label="Next page"
                disabled={meta.current_page === meta.last_page}
                onClick={() => applyQuery({ page: meta.current_page + 1 }, false)}
              >
                <ChevronRight className="size-4" />
              </PageButton>
              <PageButton
                label="Last page"
                disabled={meta.current_page === meta.last_page}
                onClick={() => applyQuery({ page: meta.last_page }, false)}
                className="hidden sm:flex"
              >
                <ChevronsRight className="size-4" />
              </PageButton>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}

function PageButton({
  label,
  className,
  disabled,
  onClick,
  children,
}: {
  label: string;
  className?: string;
  disabled: boolean;
  onClick: () => void;
  children: ReactNode;
}) {
  return (
    <button
      type="button"
      aria-label={label}
      disabled={disabled}
      onClick={onClick}
      className={cn(
        "flex size-8 items-center justify-center rounded-md border bg-background hover:bg-accent disabled:pointer-events-none disabled:opacity-50",
        className,
      )}
    >
      {children}
    </button>
  );
}
