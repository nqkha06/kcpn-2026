import { Head, useForm } from '@inertiajs/react';
import type { FormEventHandler } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin';
import adminCategories from '@/routes/admin/categories';
import type {BreadcrumbItem} from '@/types';

interface Category {
    id: number;
    name: string;
    color: string;
    description: string | null;
    status: string;
}

interface Props {
    category: Category;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Categories',
        href: adminCategories.index().url,
    },
    {
        title: 'Edit',
        href: '#',
    },
];

export default function CategoryEdit({ category }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: category.name ?? '',
        color: category.color ?? '#94A3B8',
        description: category.description ?? '',
        status: category.status ?? 'active',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(adminCategories.update(category.id).url);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Category" />
            <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">Edit Category</h2>
                    <p className="text-muted-foreground">Update category details for manual transactions.</p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Category Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="e.g. Groceries"
                                    required
                                />
                                {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="color">Color</Label>
                                <div className="flex flex-wrap items-center gap-3">
                                    <Input
                                        id="color"
                                        type="color"
                                        value={data.color}
                                        onChange={(e) => setData('color', e.target.value)}
                                        className="h-10 w-16 p-1"
                                    />
                                    <Input
                                        type="text"
                                        value={data.color}
                                        onChange={(e) => setData('color', e.target.value)}
                                        placeholder="#94A3B8"
                                        className="max-w-xs"
                                    />
                                </div>
                                {errors.color && <p className="text-sm text-destructive">{errors.color}</p>}
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="description">Description</Label>
                                <Textarea
                                    id="description"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Optional note for admin use"
                                />
                                {errors.description && <p className="text-sm text-destructive">{errors.description}</p>}
                            </div>

                            <div className="grid gap-2 max-w-xs">
                                <Label htmlFor="status">Status</Label>
                                <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                    <SelectTrigger id="status">
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.status && <p className="text-sm text-destructive">{errors.status}</p>}
                            </div>

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Save Changes
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
    );
}
