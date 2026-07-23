import {
  mergeSearchParams,
  transactionFiltersFromSearchParams,
} from "@/lib/finance/transaction-filters";
import { describe, expect, it } from "vitest";

describe("transaction URL filters", () => {
  it("parses supported filters and rejects invalid enum values", () => {
    const filters = transactionFiltersFromSearchParams(
      new URLSearchParams(
        "search=lunch&type=expense&status=invalid&wallet_id=7&sort=amount&direction=asc&page=2&per_page=25",
      ),
    );

    expect(filters).toMatchObject({
      search: "lunch",
      type: "expense",
      status: undefined,
      wallet_id: 7,
      sort: "amount",
      direction: "asc",
      page: 2,
      per_page: 25,
    });
  });

  it("updates selected URL values and removes cleared filters", () => {
    const result = mergeSearchParams(new URLSearchParams("search=food&page=3&type=expense"), {
      search: null,
      type: "income",
      page: 1,
    });

    expect(new URLSearchParams(result).get("search")).toBeNull();
    expect(new URLSearchParams(result).get("type")).toBe("income");
    expect(new URLSearchParams(result).get("page")).toBe("1");
  });
});
