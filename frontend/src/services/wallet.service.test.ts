import { walletService } from "@/services/wallet.service";
import type { FinanceWallet, WalletPayload } from "@/types/finance";
import { beforeEach, describe, expect, it, vi } from "vitest";

const wallet: FinanceWallet = {
  id: 7,
  name: "Ví chính",
  currency: "VND",
  opening_balance: 100_000,
  current_balance: 125_000,
  is_default: true,
  created_at: null,
  updated_at: null,
};

const payload: WalletPayload = {
  name: "Ví chính",
  currency: "VND",
  opening_balance: 100_000,
  is_default: true,
};

describe("walletService", () => {
  beforeEach(() => {
    vi.stubEnv("NEXT_PUBLIC_API_URL", "http://localhost:8000/api/v1");
  });

  it("loads wallets from the versioned user endpoint", async () => {
    const fetchMock = vi
      .spyOn(globalThis, "fetch")
      .mockResolvedValue(jsonResponse({ success: true, message: "ok", data: [wallet] }));

    await expect(walletService.list()).resolves.toEqual([wallet]);
    expect(fetchMock.mock.calls[0][0]).toBe("http://localhost:8000/api/v1/user/wallets");
    expect(fetchMock.mock.calls[0][1]?.method).toBe("GET");
  });

  it("creates a wallet with JSON payload", async () => {
    const fetchMock = vi
      .spyOn(globalThis, "fetch")
      .mockResolvedValue(jsonResponse({ success: true, message: "created", data: wallet }, 201));

    await expect(walletService.create(payload)).resolves.toEqual(wallet);
    expect(fetchMock.mock.calls[0][1]?.method).toBe("POST");
    expect(fetchMock.mock.calls[0][1]?.body).toBe(JSON.stringify(payload));
  });

  it("updates the selected wallet with PATCH", async () => {
    const fetchMock = vi
      .spyOn(globalThis, "fetch")
      .mockResolvedValue(jsonResponse({ success: true, message: "updated", data: wallet }));

    await walletService.update(wallet.id, payload);

    expect(fetchMock.mock.calls[0][0]).toBe(
      "http://localhost:8000/api/v1/user/wallets/7",
    );
    expect(fetchMock.mock.calls[0][1]?.method).toBe("PATCH");
  });

  it("deletes only the selected wallet", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      jsonResponse({ success: true, message: "deleted", data: null }),
    );

    await walletService.destroy(wallet.id);

    expect(fetchMock.mock.calls[0][0]).toBe(
      "http://localhost:8000/api/v1/user/wallets/7",
    );
    expect(fetchMock.mock.calls[0][1]?.method).toBe("DELETE");
  });
});

function jsonResponse(body: unknown, status = 200): Response {
  return new Response(JSON.stringify(body), {
    status,
    headers: { "Content-Type": "application/json" },
  });
}
