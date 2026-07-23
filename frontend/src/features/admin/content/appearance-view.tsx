"use client";

/* eslint-disable @next/next/no-img-element */

import { ErrorState } from "@/components/common/error-state";
import { LoadingState } from "@/components/common/loading-state";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { ApiError } from "@/lib/api/errors";
import { publicConfigurationQueryKey } from "@/services/public-site.service";
import {
    adminAppearanceQueryKey,
    adminAppearanceService,
} from "@/services/admin-content.service";
import type { AdminAppearanceGeneralEntry } from "@/types/admin";
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { useEffect, useMemo, useState, type ChangeEvent } from "react";
import { useForm } from "react-hook-form";
import { toast } from "sonner";

type AssetField = "logo_light" | "logo_dark" | "favicon" | "social_image";
interface AppearanceFormValues {
    logo_light: File | null;
    logo_dark: File | null;
    favicon: File | null;
    social_image: File | null;
    general: Record<string, AdminAppearanceGeneralEntry>;
}
const assets: Array<{ key: AssetField; label: string }> = [
    { key: "logo_light", label: "Logo Light" },
    { key: "logo_dark", label: "Logo Dark" },
    { key: "favicon", label: "Favicon" },
    { key: "social_image", label: "Social Image" },
];

export function AppearanceView() {
    const queryClient = useQueryClient();
    const [tab, setTab] = useState<"logo" | "general">("logo");
    const [activeLocale, setActiveLocale] = useState("");
    const appearanceQuery = useQuery({
        queryKey: adminAppearanceQueryKey,
        queryFn: adminAppearanceService.show,
    });
    const form = useForm<AppearanceFormValues>({
        defaultValues: {
            logo_light: null,
            logo_dark: null,
            favicon: null,
            social_image: null,
            general: {},
        },
    });
    const defaultLocale = useMemo(
        () =>
            appearanceQuery.data?.languages.find(
                (language) => language.is_default,
            )?.code ??
            appearanceQuery.data?.languages[0]?.code ??
            "en",
        [appearanceQuery.data],
    );
    const selectedLocale = activeLocale || defaultLocale;
    useEffect(() => {
        if (appearanceQuery.data)
            form.reset({
                logo_light: null,
                logo_dark: null,
                favicon: null,
                social_image: null,
                general: appearanceQuery.data.general,
            });
    }, [appearanceQuery.data, form]);
    const mutation = useMutation({
        mutationFn: adminAppearanceService.update,
        onSuccess: async (data) => {
            form.reset({
                logo_light: null,
                logo_dark: null,
                favicon: null,
                social_image: null,
                general: data.general,
            });
            await Promise.all([
                queryClient.invalidateQueries({
                    queryKey: adminAppearanceQueryKey,
                }),
                queryClient.invalidateQueries({
                    queryKey: publicConfigurationQueryKey,
                }),
            ]);
            toast.success("Appearance settings updated successfully");
        },
        onError: (error: Error) => {
            if (error instanceof ApiError) {
                assets.forEach(({ key }) => {
                    const message = error.firstError(key);
                    if (message) form.setError(key, { message });
                });
                form.setError("root", { message: error.message });
            } else
                form.setError("root", {
                    message: "Unable to save appearance settings.",
                });
        },
    });
    if (appearanceQuery.isLoading)
        return <LoadingState label="Loading appearance settings..." />;
    if (appearanceQuery.isError || !appearanceQuery.data)
        return (
            <div className="p-4">
                <ErrorState
                    message="Unable to load appearance settings."
                    onRetry={() => void appearanceQuery.refetch()}
                />
            </div>
        );
    const data = appearanceQuery.data;
    const inputClass =
        "h-9 w-full rounded-md border bg-background px-3 text-sm";
    function fileHandler(field: AssetField) {
        return (event: ChangeEvent<HTMLInputElement>) =>
            form.setValue(field, event.target.files?.[0] ?? null);
    }
    function submit(values: AppearanceFormValues): void {
        const payload = new FormData();
        assets.forEach(({ key }) => {
            if (values[key]) payload.append(key, values[key] as File);
        });
        data.languages.forEach((language) => {
            const entry = values.general[language.code] ?? {};
            (
                [
                    "site_name",
                    "site_title",
                    "tagline",
                    "meta_description",
                ] as const
            ).forEach((field) =>
                payload.append(
                    `general[${language.code}][${field}]`,
                    entry[field] ?? "",
                ),
            );
        });
        mutation.mutate(payload);
    }
    return (
        <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
            <div className="flex flex-wrap items-end justify-between gap-2">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">
                        Appearance
                    </h2>
                    <p className="text-muted-foreground">
                        Manage logos and site texts per language.
                    </p>
                </div>
                <div className="flex gap-2">
                    <Button
                        variant={tab === "logo" ? "default" : "outline"}
                        onClick={() => setTab("logo")}
                    >
                        Logo
                    </Button>
                    <Button
                        variant={tab === "general" ? "default" : "outline"}
                        onClick={() => setTab("general")}
                    >
                        General
                    </Button>
                </div>
            </div>
            <Card>
                <CardHeader>
                    <CardTitle>
                        {tab === "logo" ? "Brand Assets" : "General Info"}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form
                        onSubmit={form.handleSubmit(submit)}
                        className="space-y-6"
                        encType="multipart/form-data"
                    >
                        {tab === "logo" ? (
                            <div className="grid gap-4 md:grid-cols-2">
                                {assets.map(({ key, label }) => (
                                    <label
                                        key={key}
                                        className="grid gap-2 text-sm font-medium"
                                        htmlFor={key}
                                    >
                                        {label}
                                        <input
                                            id={key}
                                            type="file"
                                            accept="image/*,.svg,.svg+xml,.ico"
                                            onChange={fileHandler(key)}
                                            className="h-9 rounded-md border bg-background px-3 py-1 text-sm file:mr-3 file:border-0 file:bg-transparent"
                                        />
                                        {form.formState.errors[key]?.message ? (
                                            <span className="text-sm text-destructive">
                                                {
                                                    form.formState.errors[key]
                                                        ?.message
                                                }
                                            </span>
                                        ) : null}
                                        {data.logos[key]?.url ? (
                                            <div className="mt-1 flex items-start gap-3 rounded-md border border-muted p-3">
                                                <img
                                                    src={
                                                        data.logos[key].url ??
                                                        ""
                                                    }
                                                    alt={`${label} preview`}
                                                    className="size-16 shrink-0 rounded bg-muted object-contain"
                                                />
                                                <span className="break-all text-xs font-normal text-muted-foreground">
                                                    {data.logos[key].url}
                                                </span>
                                            </div>
                                        ) : null}
                                    </label>
                                ))}
                            </div>
                        ) : (
                            <div className="space-y-4">
                                <label className="flex flex-col gap-2 text-sm font-medium sm:flex-row sm:items-center">
                                    Language
                                    <select
                                        value={selectedLocale}
                                        onChange={(event) =>
                                            setActiveLocale(event.target.value)
                                        }
                                        className="h-9 w-full rounded-md border bg-background px-3 font-normal sm:w-60"
                                    >
                                        {data.languages.map((language) => (
                                            <option
                                                key={language.code}
                                                value={language.code}
                                            >
                                                {language.name} ({language.code}
                                                )
                                            </option>
                                        ))}
                                    </select>
                                </label>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <label className="grid gap-2 text-sm font-medium">
                                        Site Name
                                        <input
                                            {...form.register(
                                                `general.${selectedLocale}.site_name`,
                                            )}
                                            className={inputClass}
                                        />
                                    </label>
                                    <label className="grid gap-2 text-sm font-medium">
                                        Site Title
                                        <input
                                            {...form.register(
                                                `general.${selectedLocale}.site_title`,
                                            )}
                                            className={inputClass}
                                        />
                                    </label>
                                    <label className="grid gap-2 text-sm font-medium">
                                        Tagline
                                        <input
                                            {...form.register(
                                                `general.${selectedLocale}.tagline`,
                                            )}
                                            className={inputClass}
                                        />
                                    </label>
                                    <label className="grid gap-2 text-sm font-medium md:col-span-2">
                                        Meta Description
                                        <textarea
                                            rows={4}
                                            {...form.register(
                                                `general.${selectedLocale}.meta_description`,
                                            )}
                                            className="rounded-md border bg-background px-3 py-2 text-sm font-normal"
                                        />
                                    </label>
                                </div>
                            </div>
                        )}
                        {form.formState.errors.root?.message ? (
                            <p className="text-sm text-destructive">
                                {form.formState.errors.root.message}
                            </p>
                        ) : null}
                        <div className="flex gap-2">
                            <Button type="submit" disabled={mutation.isPending}>
                                {mutation.isPending
                                    ? "Saving..."
                                    : "Save changes"}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => history.back()}
                            >
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}
