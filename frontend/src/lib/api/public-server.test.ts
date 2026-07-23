import { getPublicPage } from "@/lib/api/public-server";
import type { PublicPage } from "@/types/site";
import { beforeEach, describe, expect, it, vi } from "vitest";

const page: PublicPage = {
  id: 1,
  title: "About",
  slug: "about",
  image: null,
  content: "<p>About us</p>",
  meta_title: "About Spendify",
  meta_description: null,
  meta_keywords: null,
  updated_at: null,
};

describe("getPublicPage", () => {
  beforeEach(() => {
    vi.stubEnv("NEXT_PUBLIC_API_URL", "http://localhost:8000/api/v1");
  });

  it("loads a published page from the public API", async () => {
    const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
      jsonResponse({ success: true, message: "ok", data: page }),
    );

    await expect(getPublicPage("about")).resolves.toEqual(page);
    expect(fetchMock.mock.calls[0][0]).toBe(
      "http://localhost:8000/api/v1/public/pages/about",
    );
  });

  it("returns null for an unpublished or missing page", async () => {
    vi.spyOn(globalThis, "fetch").mockResolvedValue(jsonResponse({}, 404));

    await expect(getPublicPage("missing")).resolves.toBeNull();
  });
});

function jsonResponse(body: unknown, status = 200): Response {
  return new Response(JSON.stringify(body), {
    status,
    headers: { "Content-Type": "application/json" },
  });
}
