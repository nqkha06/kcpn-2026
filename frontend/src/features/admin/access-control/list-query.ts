import type { QueryParams } from "@/types/api";

export function adminListQuery(
  searchParams: URLSearchParams,
  allowed: string[],
): QueryParams {
  return allowed.reduce<QueryParams>((query, key) => {
    const value = searchParams.get(key);
    if (value !== null && value !== "") query[key] = value;
    return query;
  }, {});
}
