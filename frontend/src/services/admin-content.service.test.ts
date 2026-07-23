import {
    adminAppearanceService,
    adminMenuService,
    adminPageService,
} from "@/services/admin-content.service";
import type { AdminMenuPayload, AdminPagePayload } from "@/types/admin";
import { beforeEach, describe, expect, it, vi } from "vitest";

describe("admin content services", () => {
    beforeEach(() => {
        vi.stubEnv("NEXT_PUBLIC_API_URL", "http://localhost:8000/api/v1");
    });

    it("loads pages with URL-state filters", async () => {
        const fetchMock = vi.spyOn(globalThis, "fetch").mockResolvedValue(
            jsonResponse({
                success: true,
                message: "ok",
                data: [],
                meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
                links: { first: "", last: "", prev: null, next: null },
            }),
        );

        await adminPageService.list({
            search: "about",
            status: "published",
            sort: "title",
        });

        const url = new URL(String(fetchMock.mock.calls[0][0]));
        expect(url.pathname).toBe("/api/v1/admin/pages");
        expect(url.searchParams.get("search")).toBe("about");
        expect(url.searchParams.get("status")).toBe("published");
        expect(url.searchParams.get("sort")).toBe("title");
    });

    it("creates pages and updates menus through REST endpoints", async () => {
        const fetchMock = vi.spyOn(globalThis, "fetch");
        fetchMock
            .mockResolvedValueOnce(
                jsonResponse(
                    { success: true, message: "created", data: { id: 2 } },
                    201,
                ),
            )
            .mockResolvedValueOnce(
                jsonResponse({
                    success: true,
                    message: "updated",
                    data: { id: 3 },
                }),
            );
        const page: AdminPagePayload = {
            title: "About",
            slug: "about",
            image: null,
            content: "<p>About</p>",
            meta_title: null,
            meta_description: null,
            meta_keywords: null,
            tags: "company",
            status: "published",
        };
        const menu: AdminMenuPayload = {
            title: "About",
            url: "/about",
            canonical: "home.header",
            parent_id: null,
            sort_order: 1,
            target: "_self",
            status: "active",
        };

        await adminPageService.create(page);
        await adminMenuService.update(3, menu);

        expect(fetchMock.mock.calls[0][1]?.method).toBe("POST");
        expect(fetchMock.mock.calls[0][1]?.body).toBe(JSON.stringify(page));
        expect(fetchMock.mock.calls[1][0]).toBe(
            "http://localhost:8000/api/v1/admin/menus/3",
        );
        expect(fetchMock.mock.calls[1][1]?.method).toBe("PATCH");
    });

    it("loads parent options excluding the edited menu", async () => {
        const fetchMock = vi
            .spyOn(globalThis, "fetch")
            .mockResolvedValue(
                jsonResponse({ success: true, message: "ok", data: [] }),
            );

        await adminMenuService.parentOptions(9);

        const url = new URL(String(fetchMock.mock.calls[0][0]));
        expect(url.pathname).toBe("/api/v1/admin/menus/parent-options");
        expect(url.searchParams.get("exclude")).toBe("9");
    });

    it("uploads appearance settings as FormData without forcing a content type", async () => {
        const fetchMock = vi
            .spyOn(globalThis, "fetch")
            .mockResolvedValue(
                jsonResponse({
                    success: true,
                    message: "updated",
                    data: { languages: [], logos: {}, general: {} },
                }),
            );
        const payload = new FormData();
        payload.append("general[en][site_name]", "Spendify");

        await adminAppearanceService.update(payload);

        const headers = new Headers(fetchMock.mock.calls[0][1]?.headers);
        expect(fetchMock.mock.calls[0][0]).toBe(
            "http://localhost:8000/api/v1/admin/appearance",
        );
        expect(fetchMock.mock.calls[0][1]?.body).toBe(payload);
        expect(headers.has("Content-Type")).toBe(false);
    });
});

function jsonResponse(body: unknown, status = 200): Response {
    return new Response(JSON.stringify(body), {
        status,
        headers: { "Content-Type": "application/json" },
    });
}
