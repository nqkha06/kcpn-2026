"use client";

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { RichTextEditor } from "@/components/editor/rich-text-editor";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { AdminFormField } from "@/features/admin/access-control/selection-grid";
import {
    adminPageSchema,
    type AdminPageFormValues,
} from "@/features/admin/content/admin-content-schema";
import { ApiError } from "@/lib/api/errors";
import {
    adminPageService,
    adminPagesQueryKey,
} from "@/services/admin-content.service";
import type { AdminPagePayload } from "@/types/admin";
import { zodResolver } from "@hookform/resolvers/zod";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useRouter } from "next/navigation";
import { useEffect } from "react";
import { Controller, useForm, useWatch } from "react-hook-form";
import { toast } from "sonner";

const fields = [
    "title",
    "slug",
    "image",
    "content",
    "meta_title",
    "meta_description",
    "meta_keywords",
    "tags",
    "status",
] as const;
export function PageFormView({ pageId }: { pageId?: number }) {
    const isEditing = pageId !== undefined;
    const router = useRouter();
    const queryClient = useQueryClient();
    const itemQuery = useQuery({
        queryKey: [...adminPagesQueryKey, pageId],
        queryFn: () => adminPageService.show(pageId as number),
        enabled: isEditing,
    });
    const form = useForm<AdminPageFormValues>({
        resolver: zodResolver(adminPageSchema),
        defaultValues: {
            status: "draft",
            title: "",
            slug: "",
            image: "",
            content: "",
            meta_title: "",
            meta_description: "",
            meta_keywords: "",
            tags: "",
        },
    });
    const slug = useWatch({ control: form.control, name: "slug" });
    useEffect(() => {
        if (itemQuery.data)
            form.reset({
                status: itemQuery.data.status,
                title: itemQuery.data.title,
                slug: itemQuery.data.slug ?? "",
                image: itemQuery.data.image ?? "",
                content: itemQuery.data.content ?? "",
                meta_title: itemQuery.data.meta_title ?? "",
                meta_description: itemQuery.data.meta_description ?? "",
                meta_keywords: itemQuery.data.meta_keywords ?? "",
                tags: itemQuery.data.tags.join(", "),
            });
    }, [form, itemQuery.data]);
    const mutation = useMutation({
        mutationFn: (values: AdminPageFormValues) => {
            const payload: AdminPagePayload = {
                ...values,
                slug: values.slug || null,
                image: values.image || null,
                content: values.content || null,
                meta_title: values.meta_title || null,
                meta_description: values.meta_description || null,
                meta_keywords: values.meta_keywords || null,
            };
            return isEditing
                ? adminPageService.update(pageId as number, payload)
                : adminPageService.create(payload);
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: adminPagesQueryKey,
            });
            toast.success(
                isEditing
                    ? "Page updated successfully"
                    : "Page created successfully",
            );
            router.push("/admin/pages");
        },
        onError: (error: Error) => {
            if (error instanceof ApiError)
                fields.forEach((field) => {
                    const message = error.firstError(field);
                    if (message) form.setError(field, { message });
                });
            else form.setError("root", { message: "Unable to save page." });
        },
    });
    if (isEditing && itemQuery.isLoading)
        return <LoadingState label="Loading page..." />;
    if (isEditing && (itemQuery.isError || !itemQuery.data))
        return (
            <div className="p-4">
                <ErrorState message="Unable to load page." />
            </div>
        );
    const inputClass = "h-9 rounded-md border bg-background px-3 text-sm";
    return (
        <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
            <div>
                <h2 className="text-2xl font-bold tracking-tight">
                    {isEditing ? "Edit Page" : "Create Page"}
                </h2>
                <p className="text-muted-foreground">
                    {isEditing
                        ? "Update content, metadata, and status."
                        : "Add a new page."}
                </p>
            </div>
            <form
                onSubmit={form.handleSubmit((values) =>
                    mutation.mutate(values),
                )}
                className="grid gap-4 xl:grid-cols-12"
            >
                <div className="space-y-4 xl:col-span-9">
                    <Card>
                        <CardContent className="space-y-5 pt-6">
                            <AdminFormField
                                label="Title"
                                htmlFor="title"
                                error={form.formState.errors.title?.message}
                            >
                                <input
                                    id="title"
                                    {...form.register("title")}
                                    className={inputClass}
                                />
                            </AdminFormField>
                            <AdminFormField
                                label="Permalink"
                                htmlFor="slug"
                                error={form.formState.errors.slug?.message}
                            >
                                <input
                                    id="slug"
                                    placeholder="my-awesome-page"
                                    {...form.register("slug")}
                                    className={inputClass}
                                />
                                {slug ? (
                                    <p className="text-xs text-muted-foreground">
                                        Preview: /p/{slug}
                                    </p>
                                ) : null}
                            </AdminFormField>
                            <AdminFormField
                                label="Description"
                                htmlFor="meta_description"
                                error={
                                    form.formState.errors.meta_description
                                        ?.message
                                }
                            >
                                <textarea
                                    id="meta_description"
                                    rows={3}
                                    {...form.register("meta_description")}
                                    className="rounded-md border bg-background px-3 py-2 text-sm"
                                />
                            </AdminFormField>
                            <AdminFormField
                                label="Content"
                                htmlFor="content"
                                error={form.formState.errors.content?.message}
                            >
                                <Controller
                                    control={form.control}
                                    name="content"
                                    render={({ field }) => (
                                        <RichTextEditor
                                            value={field.value}
                                            onChange={field.onChange}
                                            placeholder="Write your page content..."
                                        />
                                    )}
                                />
                            </AdminFormField>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>SEO & Media</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4 md:grid-cols-2">
                            <AdminFormField
                                label="Image URL"
                                htmlFor="image"
                                error={form.formState.errors.image?.message}
                            >
                                <input
                                    id="image"
                                    placeholder="https://..."
                                    {...form.register("image")}
                                    className={inputClass}
                                />
                            </AdminFormField>
                            <AdminFormField
                                label="Tags (comma separated)"
                                htmlFor="tags"
                                error={form.formState.errors.tags?.message}
                            >
                                <input
                                    id="tags"
                                    placeholder="news, releases"
                                    {...form.register("tags")}
                                    className={inputClass}
                                />
                            </AdminFormField>
                            <AdminFormField
                                label="Meta Title"
                                htmlFor="meta_title"
                                error={
                                    form.formState.errors.meta_title?.message
                                }
                            >
                                <input
                                    id="meta_title"
                                    {...form.register("meta_title")}
                                    className={inputClass}
                                />
                            </AdminFormField>
                            <AdminFormField
                                label="Meta Keywords"
                                htmlFor="meta_keywords"
                                error={
                                    form.formState.errors.meta_keywords?.message
                                }
                            >
                                <input
                                    id="meta_keywords"
                                    placeholder="seo, marketing"
                                    {...form.register("meta_keywords")}
                                    className={inputClass}
                                />
                            </AdminFormField>
                        </CardContent>
                    </Card>
                </div>
                <div className="space-y-4 xl:col-span-3">
                    <Card>
                        <CardHeader>
                            <CardTitle>Publish</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3">
                            <Button
                                type="submit"
                                className="w-full"
                                disabled={mutation.isPending}
                            >
                                Save
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                className="w-full"
                                onClick={() => router.back()}
                            >
                                Cancel
                            </Button>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>Status</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <select
                                {...form.register("status")}
                                className="h-9 w-full rounded-md border bg-background px-3 text-sm"
                            >
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                                <option value="pending">Pending</option>
                            </select>
                        </CardContent>
                    </Card>
                    {form.formState.errors.root?.message ? (
                        <p className="text-sm text-destructive">
                            {form.formState.errors.root.message}
                        </p>
                    ) : null}
                </div>
            </form>
        </div>
    );
}
