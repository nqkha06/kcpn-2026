import { categoryService } from "@/services/category.service";
import { beforeEach, describe, expect, it, vi } from "vitest";

describe("private category service", () => {
  beforeEach(() => {
    vi.stubEnv("NEXT_PUBLIC_API_URL", "http://localhost:8000/api/v1");
  });

  it("creates and updates categories through the user API", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch");
    fetchMock
      .mockResolvedValueOnce(jsonResponse({ success: true, message: "created", data: { id: 5 } }, 201))
      .mockResolvedValueOnce(jsonResponse({ success: true, message: "updated", data: { id: 5 } }));
    const payload = { name: "Thú cưng", color: "#0EA5E9", description: null };

    await categoryService.create(payload);
    await categoryService.update(5, payload);

    expect(fetchMock.mock.calls[0][0]).toBe("http://localhost:8000/api/v1/user/categories");
    expect(fetchMock.mock.calls[0][1]?.method).toBe("POST");
    expect(fetchMock.mock.calls[1][0]).toBe("http://localhost:8000/api/v1/user/categories/5");
    expect(fetchMock.mock.calls[1][1]?.method).toBe("PATCH");
  });

  it("deletes only through the centralized category service", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      jsonResponse({ success: true, message: "deleted", data: {} }),
    );

    await categoryService.destroy(5);

    expect(fetchMock.mock.calls[0][0]).toBe("http://localhost:8000/api/v1/user/categories/5");
    expect(fetchMock.mock.calls[0][1]?.method).toBe("DELETE");
  });
});

function jsonResponse(body: unknown, status = 200): Response {
  return new Response(JSON.stringify(body), {
    status,
    headers: { "Content-Type": "application/json" },
  });
}
