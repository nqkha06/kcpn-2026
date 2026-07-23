import { transactionSchema } from "@/features/transactions/transaction-schema";
import { describe, expect, it } from "vitest";

describe("transactionSchema", () => {
  it("accepts a complete transaction form", () => {
    const result = transactionSchema.parse({
      type: "expense",
      amount: "250000",
      wallet_id: "4",
      category_id: "none",
      transacted_at: "2026-07-22",
      note: "Ăn trưa",
      labels: "food, office",
    });

    expect(result).toMatchObject({ type: "expense", wallet_id: "4", amount: "250000" });
  });

  it("rejects a non-positive amount and labels over 30 characters", () => {
    const result = transactionSchema.safeParse({
      type: "income",
      amount: "0",
      wallet_id: "",
      category_id: "none",
      transacted_at: "",
      note: "",
      labels: "a-label-that-is-definitely-longer-than-thirty-characters",
    });

    expect(result.success).toBe(false);
    expect(result.error?.flatten().fieldErrors).toMatchObject({
      amount: ["Số tiền phải lớn hơn 0."],
      wallet_id: ["Vui lòng chọn ví."],
      transacted_at: ["Vui lòng chọn ngày giao dịch."],
      labels: ["Mỗi nhãn tối đa 30 ký tự."],
    });
  });
});
