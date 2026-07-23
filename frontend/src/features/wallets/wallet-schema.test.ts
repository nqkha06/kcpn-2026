import { walletSchema } from "@/features/wallets/wallet-schema";
import { describe, expect, it } from "vitest";

describe("walletSchema", () => {
  it("normalizes a valid currency code", () => {
    const result = walletSchema.parse({
      name: "Ví tiền mặt",
      currency: "vnd",
      opening_balance: "100000",
      is_default: true,
    });

    expect(result.currency).toBe("VND");
  });

  it("rejects invalid currency and balance values", () => {
    const result = walletSchema.safeParse({
      name: "",
      currency: "VN",
      opening_balance: "không phải số",
      is_default: false,
    });

    expect(result.success).toBe(false);
    expect(result.error?.flatten().fieldErrors).toMatchObject({
      name: ["Vui lòng nhập tên ví."],
      currency: ["Tiền tệ phải là mã gồm 3 ký tự."],
      opening_balance: ["Số dư phải là một số."],
    });
  });
});
