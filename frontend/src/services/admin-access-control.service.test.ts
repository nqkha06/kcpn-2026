import {
  adminPermissionService,
  adminRoleService,
  adminUserService,
} from "@/services/admin-access-control.service";
import { beforeEach, describe, expect, it, vi } from "vitest";

describe("admin access control services", () => {
  beforeEach(() => {
    vi.stubEnv("NEXT_PUBLIC_API_URL", "http://localhost:8000/api/v1");
  });

  it("loads users with URL-state filters", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      jsonResponse({
        success: true,
        message: "ok",
        data: [],
        meta: { current_page: 2, last_page: 3, per_page: 15, total: 31 },
        links: { first: "", last: "", prev: "", next: "" },
      }),
    );

    await adminUserService.list({ search: "admin", role: "admin", page: 2 });

    const url = new URL(String(fetchMock.mock.calls[0][0]));
    expect(url.pathname).toBe("/api/v1/admin/users");
    expect(url.searchParams.get("search")).toBe("admin");
    expect(url.searchParams.get("role")).toBe("admin");
    expect(url.searchParams.get("page")).toBe("2");
  });

  it("creates and updates roles with permission ids", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch");
    fetchMock
      .mockResolvedValueOnce(jsonResponse({ success: true, message: "created", data: { id: 4 } }, 201))
      .mockResolvedValueOnce(jsonResponse({ success: true, message: "updated", data: { id: 4 } }));
    const payload = { name: "editor", permissions: [1, 2] };

    await adminRoleService.create(payload);
    await adminRoleService.update(4, payload);

    expect(fetchMock.mock.calls[0][1]?.method).toBe("POST");
    expect(fetchMock.mock.calls[0][1]?.body).toBe(JSON.stringify(payload));
    expect(fetchMock.mock.calls[1][0]).toBe("http://localhost:8000/api/v1/admin/roles/4");
    expect(fetchMock.mock.calls[1][1]?.method).toBe("PATCH");
  });

  it("deletes permissions through the centralized client", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      jsonResponse({ success: true, message: "deleted", data: {} }),
    );

    await adminPermissionService.destroy(9);

    expect(fetchMock.mock.calls[0][0]).toBe(
      "http://localhost:8000/api/v1/admin/permissions/9",
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
