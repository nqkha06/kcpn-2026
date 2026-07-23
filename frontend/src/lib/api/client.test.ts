import { apiClient, UNAUTHENTICATED_EVENT } from "@/lib/api/client";
import { UnauthenticatedError, ValidationError } from "@/lib/api/errors";
import { beforeEach, describe, expect, it, vi } from "vitest";

describe("apiClient", () => {
  beforeEach(() => {
    vi.stubEnv("NEXT_PUBLIC_API_URL", "http://localhost:8000/api/v1");
    document.cookie = "XSRF-TOKEN=encoded%20token; path=/";
  });

  it("sends JSON, credentials and the decoded XSRF token", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      jsonResponse({ success: true, message: "ok", data: { id: 1 } }),
    );

    await apiClient.post("/users", { name: "Lan" });

    const [, options] = fetchMock.mock.calls[0];
    const headers = new Headers(options?.headers);
    expect(options?.credentials).toBe("include");
    expect(options?.body).toBe(JSON.stringify({ name: "Lan" }));
    expect(headers.get("Content-Type")).toBe("application/json");
    expect(headers.get("X-XSRF-TOKEN")).toBe("encoded token");
  });

  it("keeps FormData content type under browser control", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      jsonResponse({ success: true, message: "ok", data: {} }),
    );
    const body = new FormData();
    body.set("name", "Logo");

    await apiClient.post("/appearance", body);

    const [, options] = fetchMock.mock.calls[0];
    const headers = new Headers(options?.headers);
    expect(options?.body).toBe(body);
    expect(headers.has("Content-Type")).toBe(false);
  });

  it("turns 422 responses into field-aware ValidationError objects", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(
      jsonResponse(
        {
          success: false,
          message: "Validation failed",
          errors: { email: ["Email already exists"] },
        },
        422,
      ),
    );

    await expect(apiClient.post("/auth/register", {})).rejects.toMatchObject({
      name: "ValidationError",
      status: 422,
      errors: { email: ["Email already exists"] },
    } satisfies Partial<ValidationError>);
  });

  it("emits one centralized event for unauthenticated responses", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(
      jsonResponse({ success: false, message: "Unauthenticated", errors: {} }, 401),
    );
    const listener = vi.fn();
    window.addEventListener(UNAUTHENTICATED_EVENT, listener);

    await expect(apiClient.get("/auth/me")).rejects.toBeInstanceOf(UnauthenticatedError);
    expect(listener).toHaveBeenCalledOnce();

    window.removeEventListener(UNAUTHENTICATED_EVENT, listener);
  });
});

function jsonResponse(payload: unknown, status = 200): Response {
  return new Response(JSON.stringify(payload), {
    status,
    headers: { "Content-Type": "application/json" },
  });
}
