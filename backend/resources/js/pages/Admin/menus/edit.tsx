import { Head, router, useForm } from '@inertiajs/react';
import type { FormEventHandler } from 'react';
import { useMemo } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AdminLayout from '@/layouts/admin';
import adminMenus from '@/routes/admin/menus';
import type { BreadcrumbItem } from '@/types';

interface MenuPayload {
    id: number;
    title: string;
    url: string | null;
    canonical: string;
    parent_id: number | null;
    sort_order: number;
    target: '_self' | '_blank';
    status: 'active' | 'inactive';
}

interface ParentMenuOption {
    id: number;
    title: string;
    canonical: string;
}

interface Props {
    menu: MenuPayload;
    parentMenus: ParentMenuOption[];
}

const NO_PARENT_VALUE = '__none__';

export default function EditMenu({ menu, parentMenus }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Menus', href: adminMenus.index().url },
        { title: 'Edit', href: adminMenus.edit(menu.id).url },
    ];

    const { data, setData, put, processing, errors } = useForm({
        title: menu.title,
        url: menu.url || '',
        canonical: menu.canonical,
        parent_id: menu.parent_id,
        sort_order: menu.sort_order,
        target: menu.target,
        status: menu.status,
    });

    const availableParents = useMemo(() => {
        return parentMenus.filter((item) => item.canonical === data.canonical);
    }, [data.canonical, parentMenus]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(adminMenus.update(menu.id).url);
    };

    const submitAndExit = () => {
        put(adminMenus.update(menu.id).url, {
            onSuccess: () => {
                router.visit(adminMenus.index().url);
            },
        });
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Menu #${menu.id}`} />
            <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">Edit Menu</h2>
                    <p className="text-muted-foreground">Update menu details and canonical slot assignment.</p>
                </div>

                <form onSubmit={submit} className="grid gap-4 xl:grid-cols-12">
                    <div className="space-y-4 xl:col-span-9">
                        <Card>
                            <CardContent className="space-y-5 pt-6">
                                <div className="grid gap-2">
                                    <Label htmlFor="title">Title</Label>
                                    <Input id="title" value={data.title} onChange={(e) => setData('title', e.target.value)} required />
                                    {errors.title && <p className="text-sm text-destructive">{errors.title as string}</p>}
                                </div>

                                <div className="grid gap-2 md:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="url">URL</Label>
                                        <Input
                                            id="url"
                                            value={data.url}
                                            onChange={(e) => setData('url', e.target.value)}
                                            placeholder="/about or https://example.com"
                                        />
                                        {errors.url && <p className="text-sm text-destructive">{errors.url as string}</p>}
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="sort_order">Sort Order</Label>
                                        <Input
                                            id="sort_order"
                                            type="number"
                                            min={0}
                                            value={data.sort_order}
                                            onChange={(e) => setData('sort_order', Number(e.target.value || 0))}
                                        />
                                        {errors.sort_order && <p className="text-sm text-destructive">{errors.sort_order as string}</p>}
                                    </div>
                                </div>

                                <div className="grid gap-2 md:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label htmlFor="canonical">Canonical</Label>
                                        <Input
                                            id="canonical"
                                            value={data.canonical}
                                            onChange={(e) => {
                                                setData('canonical', e.target.value);
                                                setData('parent_id', null);
                                            }}
                                            placeholder="home.header"
                                            required
                                        />
                                        {errors.canonical && <p className="text-sm text-destructive">{errors.canonical as string}</p>}
                                    </div>

                                    <div className="grid gap-2">
                                        <Label>Parent Menu</Label>
                                        <Select
                                            value={data.parent_id === null ? NO_PARENT_VALUE : String(data.parent_id)}
                                            onValueChange={(value) => setData('parent_id', value === NO_PARENT_VALUE ? null : Number(value))}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="No parent" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value={NO_PARENT_VALUE}>No parent</SelectItem>
                                                {availableParents.map((parent) => (
                                                    <SelectItem key={parent.id} value={String(parent.id)}>
                                                        {parent.title}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.parent_id && <p className="text-sm text-destructive">{errors.parent_id as string}</p>}
                                    </div>
                                </div>

                                <div className="grid gap-2 md:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label>Target</Label>
                                        <Select value={data.target} onValueChange={(value) => setData('target', value as '_self' | '_blank')}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select target" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="_self">Same Tab (_self)</SelectItem>
                                                <SelectItem value="_blank">New Tab (_blank)</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.target && <p className="text-sm text-destructive">{errors.target as string}</p>}
                                    </div>

                                    <div className="grid gap-2">
                                        <Label>Status</Label>
                                        <Select value={data.status} onValueChange={(value) => setData('status', value as 'active' | 'inactive')}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="active">Active</SelectItem>
                                                <SelectItem value="inactive">Inactive</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        {errors.status && <p className="text-sm text-destructive">{errors.status as string}</p>}
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-4 xl:col-span-3">
                        <Card>
                            <CardHeader>
                                <CardTitle>Publish</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <Button type="submit" className="w-full" disabled={processing}>Save</Button>
                                <Button type="button" variant="outline" className="w-full" onClick={submitAndExit} disabled={processing}>
                                    Save & Exit
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
