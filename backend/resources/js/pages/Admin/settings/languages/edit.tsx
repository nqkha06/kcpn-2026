import { Head, useForm } from '@inertiajs/react'
import type { FormEventHandler } from 'react'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Switch } from '@/components/ui/switch'
import AdminLayout from '@/layouts/admin'
import adminLanguages from '@/routes/admin/settings/languages'
import type {BreadcrumbItem} from '@/types';

interface Language {
    id: number
    name: string
    locale: string
    code: string
    flag?: string | null
    is_default: boolean
    is_rtl: boolean
    order: number
}

interface Props {
    language: Language
}

export default function EditLanguage({ language }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Languages', href: adminLanguages.index().url },
        { title: language.name, href: adminLanguages.edit(language.id).url },
    ]

    const { data, setData, put, processing, errors } = useForm({
        name: language.name,
        locale: language.locale,
        code: language.code,
        flag: language.flag || '',
        is_default: language.is_default,
        is_rtl: language.is_rtl,
        order: language.order,
    })

    const submit: FormEventHandler = (e) => {
        e.preventDefault()
        put(adminLanguages.update(language.id).url)
    }

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${language.name}`} />
            <div className="flex flex-1 flex-col gap-4 sm:gap-6 p-4">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">Edit Language</h2>
                    <p className="text-muted-foreground">Update language details.</p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Language Info</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="grid gap-4 md:grid-cols-2">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} required />
                                {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="locale">Locale</Label>
                                <Input id="locale" value={data.locale} onChange={(e) => setData('locale', e.target.value)} required />
                                {errors.locale && <p className="text-sm text-destructive">{errors.locale}</p>}
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="code">Code</Label>
                                <Input id="code" value={data.code} onChange={(e) => setData('code', e.target.value)} required />
                                {errors.code && <p className="text-sm text-destructive">{errors.code}</p>}
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="flag">Flag URL</Label>
                                <Input id="flag" value={data.flag} onChange={(e) => setData('flag', e.target.value)} />
                                {errors.flag && <p className="text-sm text-destructive">{errors.flag}</p>}
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="order">Order</Label>
                                <Input id="order" type="number" value={data.order} onChange={(e) => setData('order', Number(e.target.value))} min={0} />
                                {errors.order && <p className="text-sm text-destructive">{errors.order}</p>}
                            </div>

                            <div className="flex items-center gap-2">
                                <Switch checked={data.is_default} onCheckedChange={(v) => setData('is_default', v)} />
                                <Label htmlFor="is_default">Default</Label>
                            </div>

                            <div className="flex items-center gap-2">
                                <Switch checked={data.is_rtl} onCheckedChange={(v) => setData('is_rtl', v)} />
                                <Label htmlFor="is_rtl">RTL</Label>
                            </div>

                            <div className="md:col-span-2 flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Save
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
