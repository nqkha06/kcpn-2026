import { Head, useForm } from '@inertiajs/react'
import { useMemo, useState } from 'react'
import type { FormEventHandler, ChangeEvent } from 'react';
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import AdminLayout from '@/layouts/admin'
import adminAppearance from '@/routes/admin/settings/appearance/options'
import type {BreadcrumbItem} from '@/types';

interface Language {
    id: number
    name: string
    code: string
    locale: string
    is_default: boolean
}

interface LogoItem {
    path?: string | null
    url?: string | null
}

interface GeneralEntry {
    site_name?: string
    site_title?: string
    tagline?: string
    meta_description?: string
}

interface Props {
    languages: Language[]
    logos: Record<'logo_light' | 'logo_dark' | 'favicon' | 'social_image', LogoItem>
    general: Record<string, GeneralEntry>
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Appearance', href: adminAppearance.index().url },
    { title: 'Options', href: adminAppearance.index().url },
]

export default function AppearanceOptions({ languages, logos, general }: Props) {
    const defaultLocale = useMemo(() => {
        return languages.find((l) => l.is_default)?.code || languages[0]?.code || 'en'
    }, [languages])

    const [tab, setTab] = useState<'logo' | 'general'>('logo')
    const [activeLocale, setActiveLocale] = useState<string>(defaultLocale)

    const { data, setData, post, processing, errors } = useForm({
        logo_light: null as File | null,
        logo_dark: null as File | null,
        favicon: null as File | null,
        social_image: null as File | null,
        general: general || {},
    })

    const localeEntry: GeneralEntry = data.general?.[activeLocale] || {}

    const updateGeneral = (field: keyof GeneralEntry, value: string) => {
        setData('general', {
            ...(data.general || {}),
            [activeLocale]: {
                ...(data.general?.[activeLocale] || {}),
                [field]: value,
            },
        })
    }

    const handleFile = (key: 'logo_light' | 'logo_dark' | 'favicon' | 'social_image') =>
        (e: ChangeEvent<HTMLInputElement>) => {
            const file = e.target.files?.[0] ?? null
            setData(key, file)
        }

    const submit: FormEventHandler = (e) => {
        e.preventDefault()
        post(adminAppearance.store().url, { forceFormData: true, preserveScroll: true })
    }

    const renderPreview = (item?: LogoItem, label?: string) => {
        if (!item?.url) return null
        return (
            <div className="mt-3 flex items-start gap-3 rounded-md border border-muted p-3">
                <img
                    src={item.url}
                    alt={(label || 'Current image') + ' preview'}
                    className="h-16 w-16 shrink-0 rounded bg-muted object-contain"
                />
                <div className="text-xs text-muted-foreground break-all">{item.url}</div>
            </div>
        )
    }

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Appearance Options" />
            <div className="flex flex-1 flex-col gap-4 sm:gap-6 p-4">
                <div className="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight">Appearance</h2>
                        <p className="text-muted-foreground">Manage logos and site texts per language.</p>
                    </div>
                    <div className="flex gap-2">
                        <Button variant={tab === 'logo' ? 'default' : 'outline'} onClick={() => setTab('logo')}>
                            Logo
                        </Button>
                        <Button variant={tab === 'general' ? 'default' : 'outline'} onClick={() => setTab('general')}>
                            General
                        </Button>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{tab === 'logo' ? 'Brand Assets' : 'General Info'}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-6" encType="multipart/form-data">
                            {tab === 'logo' && (
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="logo_light">Logo Light</Label>
                                        <Input id="logo_light" type="file" accept="image/*,.svg,.svg+xml" onChange={handleFile('logo_light')} />
                                        {errors.logo_light && <p className="text-sm text-destructive">{errors.logo_light}</p>}
                                        {renderPreview(logos.logo_light, 'Logo light')}
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="logo_dark">Logo Dark</Label>
                                        <Input id="logo_dark" type="file" accept="image/*,.svg,.svg+xml" onChange={handleFile('logo_dark')} />
                                        {errors.logo_dark && <p className="text-sm text-destructive">{errors.logo_dark}</p>}
                                        {renderPreview(logos.logo_dark, 'Logo dark')}
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="favicon">Favicon</Label>
                                        <Input id="favicon" type="file" accept="image/*,.svg,.svg+xml" onChange={handleFile('favicon')} />
                                        {errors.favicon && <p className="text-sm text-destructive">{errors.favicon}</p>}
                                        {renderPreview(logos.favicon, 'Favicon')}
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="social_image">Social Image</Label>
                                        <Input id="social_image" type="file" accept="image/*,.svg,.svg+xml" onChange={handleFile('social_image')} />
                                        {errors.social_image && <p className="text-sm text-destructive">{errors.social_image}</p>}
                                        {renderPreview(logos.social_image, 'Social image')}
                                    </div>
                                </div>
                            )}

                            {tab === 'general' && (
                                <div className="space-y-4">
                                    <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
                                        <Label className="text-sm font-medium">Language</Label>
                                        <Select value={activeLocale} onValueChange={setActiveLocale}>
                                            <SelectTrigger className="w-full sm:w-60">
                                                <SelectValue placeholder="Select language" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {languages.map((lang) => (
                                                    <SelectItem key={lang.code} value={lang.code}>
                                                        {lang.name} ({lang.code})
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>

                                    <div className="grid gap-4 md:grid-cols-2">
                                        <div className="grid gap-2">
                                            <Label htmlFor="site_name">Site Name</Label>
                                            <Input
                                                id="site_name"
                                                value={localeEntry.site_name || ''}
                                                onChange={(e) => updateGeneral('site_name', e.target.value)}
                                            />
                                            {errors[`general.${activeLocale}.site_name` as keyof typeof errors] && (
                                                <p className="text-sm text-destructive">
                                                    {errors[`general.${activeLocale}.site_name` as keyof typeof errors] as string}
                                                </p>
                                            )}
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="site_title">Site Title</Label>
                                            <Input
                                                id="site_title"
                                                value={localeEntry.site_title || ''}
                                                onChange={(e) => updateGeneral('site_title', e.target.value)}
                                            />
                                            {errors[`general.${activeLocale}.site_title` as keyof typeof errors] && (
                                                <p className="text-sm text-destructive">
                                                    {errors[`general.${activeLocale}.site_title` as keyof typeof errors] as string}
                                                </p>
                                            )}
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="tagline">Tagline</Label>
                                            <Input
                                                id="tagline"
                                                value={localeEntry.tagline || ''}
                                                onChange={(e) => updateGeneral('tagline', e.target.value)}
                                            />
                                            {errors[`general.${activeLocale}.tagline` as keyof typeof errors] && (
                                                <p className="text-sm text-destructive">
                                                    {errors[`general.${activeLocale}.tagline` as keyof typeof errors] as string}
                                                </p>
                                            )}
                                        </div>

                                        <div className="grid gap-2 md:col-span-2">
                                            <Label htmlFor="meta_description">Meta Description</Label>
                                            <Textarea
                                                id="meta_description"
                                                value={localeEntry.meta_description || ''}
                                                onChange={(e) => updateGeneral('meta_description', e.target.value)}
                                                rows={4}
                                            />
                                            {errors[`general.${activeLocale}.meta_description` as keyof typeof errors] && (
                                                <p className="text-sm text-destructive">
                                                    {errors[`general.${activeLocale}.meta_description` as keyof typeof errors] as string}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            )}

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Save changes
                                </Button>
                                <Button type="button" variant="outline" onClick={() => window.history.back()}>
                                    Cancel
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AdminLayout>
    )
}
