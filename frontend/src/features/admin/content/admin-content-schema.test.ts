import {
    adminMenuSchema,
    adminPageSchema,
} from "@/features/admin/content/admin-content-schema";
import { describe, expect, it } from "vitest";

describe("admin content schemas", () => {
    it("requires a page title and accepts all supported statuses", () => {
        const page = {
            title: "About us",
            slug: "about-us",
            image: "",
            content: "<p>About</p>",
            meta_title: "About",
            meta_description: "Description",
            meta_keywords: "about, company",
            tags: "company",
            status: "published" as const,
        };

        expect(adminPageSchema.safeParse(page).success).toBe(true);
        expect(adminPageSchema.safeParse({ ...page, title: "" }).success).toBe(
            false,
        );
    });

    it("matches the backend menu canonical and sort-order rules", () => {
        const menu = {
            title: "About",
            url: "/about",
            canonical: "home.header",
            parent_id: "",
            sort_order: "10",
            target: "_self" as const,
            status: "active" as const,
        };

        expect(adminMenuSchema.safeParse(menu).success).toBe(true);
        expect(
            adminMenuSchema.safeParse({ ...menu, canonical: "header" }).success,
        ).toBe(false);
        expect(
            adminMenuSchema.safeParse({ ...menu, sort_order: "10000" }).success,
        ).toBe(false);
    });
});
