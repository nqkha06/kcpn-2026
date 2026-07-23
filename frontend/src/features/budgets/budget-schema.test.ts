import { budgetSchema } from "@/features/budgets/budget-schema";
import { describe, expect, it } from "vitest";

describe("budgetSchema", () => {
  it("accepts a valid monthly budget", () => {
    expect(
      budgetSchema.parse({
        category_id: "2",
        amount_limit: "1500000",
        period: "monthly",
        note: "Chi tiêu gia đình",
      }),
    ).toMatchObject({ category_id: "2", period: "monthly" });
  });

  it("rejects an empty category and invalid limit", () => {
    const result = budgetSchema.safeParse({
      category_id: "",
      amount_limit: "-1",
      period: "yearly",
      note: "",
    });

    expect(result.success).toBe(false);
    expect(result.error?.flatten().fieldErrors).toMatchObject({
      category_id: ["Vui lòng chọn danh mục."],
      amount_limit: ["Hạn mức phải lớn hơn 0."],
    });
  });
});
