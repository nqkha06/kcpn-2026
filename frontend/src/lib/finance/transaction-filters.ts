import type { TransactionFilters } from "@/types/finance";

const transactionTypes = ["income", "expense"] as const;
const transactionStatuses = ["posted", "pending"] as const;
const sortFields = ["transacted_at", "amount", "created_at"] as const;
const sortDirections = ["asc", "desc"] as const;

function positiveInteger(value: string | null, fallback: number): number {
  const parsed = Number(value);

  return Number.isInteger(parsed) && parsed > 0 ? parsed : fallback;
}

function oneOf<T extends string>(value: string | null, options: readonly T[]): T | undefined {
  return options.includes(value as T) ? (value as T) : undefined;
}

export function transactionFiltersFromSearchParams(searchParams: URLSearchParams): TransactionFilters {
  const perPage = Math.min(100, positiveInteger(searchParams.get("per_page"), 15));
  const walletId = positiveInteger(searchParams.get("wallet_id"), 0);
  const categoryId = positiveInteger(searchParams.get("category_id"), 0);

  return {
    search: searchParams.get("search")?.trim() || undefined,
    type: oneOf(searchParams.get("type"), transactionTypes),
    status: oneOf(searchParams.get("status"), transactionStatuses),
    wallet_id: walletId || undefined,
    category_id: categoryId || undefined,
    date_from: searchParams.get("date_from") || undefined,
    date_to: searchParams.get("date_to") || undefined,
    sort: oneOf(searchParams.get("sort"), sortFields) ?? "transacted_at",
    direction: oneOf(searchParams.get("direction"), sortDirections) ?? "desc",
    page: positiveInteger(searchParams.get("page"), 1),
    per_page: perPage,
  };
}

export function mergeSearchParams(
  current: URLSearchParams,
  updates: Record<string, string | number | null | undefined>,
): string {
  const next = new URLSearchParams(current.toString());

  Object.entries(updates).forEach(([key, value]) => {
    if (value === null || value === undefined || value === "") {
      next.delete(key);
    } else {
      next.set(key, String(value));
    }
  });

  return next.toString();
}
