export function resolveCurrencyCode(currency?: string | null): string {
  const normalizedCurrency = currency?.trim().toUpperCase();

  return normalizedCurrency && normalizedCurrency.length === 3 ? normalizedCurrency : "VND";
}

export function formatCurrencyAmount(
  amount: number,
  currency?: string | null,
  maximumFractionDigits = 2,
): string {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: resolveCurrencyCode(currency),
    maximumFractionDigits,
  }).format(amount);
}

export function userInitials(name?: string | null): string {
  const initials = (name ?? "")
    .trim()
    .split(/\s+/)
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join("");

  return initials || "U";
}
