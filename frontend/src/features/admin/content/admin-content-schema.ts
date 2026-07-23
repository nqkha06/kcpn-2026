import { z } from "zod";

export const adminPageSchema = z.object({
    title: z.string().trim().min(1, "Title is required.").max(255),
    slug: z.string().trim().max(255),
    image: z.string().trim().max(2048),
    content: z.string(),
    meta_title: z.string().trim().max(255),
    meta_description: z.string(),
    meta_keywords: z.string(),
    tags: z.string(),
    status: z.enum(["published", "draft", "pending"]),
});

export const adminMenuSchema = z.object({
    title: z.string().trim().min(1, "Please enter a menu title.").max(120),
    url: z.string().trim().max(255),
    canonical: z
        .string()
        .trim()
        .regex(
            /^[a-z0-9]+(\.[a-z0-9_-]+)+$/,
            "Canonical must look like home.header, home.footer, or user.header.",
        ),
    parent_id: z.string(),
    sort_order: z
        .string()
        .refine(
            (value) =>
                Number.isInteger(Number(value)) &&
                Number(value) >= 0 &&
                Number(value) <= 9999,
            "Sort order must be between 0 and 9999.",
        ),
    target: z.enum(["_self", "_blank"]),
    status: z.enum(["active", "inactive"]),
});

export type AdminPageFormValues = z.infer<typeof adminPageSchema>;
export type AdminMenuFormValues = z.infer<typeof adminMenuSchema>;
