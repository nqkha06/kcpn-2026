import { Head, useForm } from '@inertiajs/react';
import type { FormEventHandler } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin';
import adminPermissions from '@/routes/admin/permissions';
import type {BreadcrumbItem} from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Permissions',
        href: adminPermissions.index().url,
    },
    {
        title: 'Create',
        href: adminPermissions.create().url,
    },
];

export default function PermissionAdd() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(adminPermissions.store().url);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Permission" />
            <div className="flex flex-1 flex-col gap-4 sm:gap-6 p-4">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">Create Permission</h2>
                    <p className="text-muted-foreground">
                        Add a new permission to the system.
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Permission Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid gap-2 max-w-xl">
                                <Label htmlFor="name">Permission Name</Label>
                                <Input
                                    id="name"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    placeholder="e.g. edit articles"
                                    required
                                />
                                {errors.name && (
                                    <p className="text-sm text-destructive">{errors.name}</p>
                                )}
                            </div>

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Create Permission
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => window.history.back()}
                                >
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
