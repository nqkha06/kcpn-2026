import { settingsService } from "@/services/settings.service";
import type { UserSettingsData } from "@/types/finance";
import { beforeEach, describe, expect, it, vi } from "vitest";

const settings: UserSettingsData = {
  profile: { name: "Jane Doe", email: "jane@example.com" },
  preferences: { currency: "VND" },
  currency_options: [{ code: "VND", label: "VND (₫)" }],
};

describe("settingsService", () => {
  beforeEach(() => {
    vi.stubEnv("NEXT_PUBLIC_API_URL", "http://localhost:8000/api/v1");
  });

  it("loads user settings from the versioned endpoint", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      jsonResponse({ success: true, message: "ok", data: settings }),
    );

    await expect(settingsService.show()).resolves.toEqual(settings);
    expect(fetchMock.mock.calls[0][0]).toBe("http://localhost:8000/api/v1/user/settings");
  });

  it("updates profile and preferences using PATCH", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch");
    fetchMock
      .mockResolvedValueOnce(
        jsonResponse({ success: true, message: "updated", data: settings.profile }),
      )
      .mockResolvedValueOnce(
        jsonResponse({ success: true, message: "updated", data: settings }),
      );

    await settingsService.updateProfile(settings.profile);
    await settingsService.updatePreferences({ currency: "VND" });

    expect(fetchMock.mock.calls[0][1]?.method).toBe("PATCH");
    expect(fetchMock.mock.calls[0][1]?.body).toBe(JSON.stringify(settings.profile));
    expect(fetchMock.mock.calls[1][1]?.method).toBe("PATCH");
    expect(fetchMock.mock.calls[1][1]?.body).toBe(JSON.stringify({ currency: "VND" }));
  });
});

function jsonResponse(body: unknown, status = 200): Response {
  return new Response(JSON.stringify(body), {
    status,
    headers: { "Content-Type": "application/json" },
  });
}
