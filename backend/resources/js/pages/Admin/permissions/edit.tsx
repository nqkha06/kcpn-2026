import { Head, useForm } from '@inertiajs/react';
import type { FormEventHandler } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin';
import adminPermissions from '@/routes/admin/permissions';
import type {BreadcrumbItem} from '@/types';

interface Props {
    permission: {
        id: number;
        name: string;
    };
}

export default function PermissionEdit({ permission }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Permissions',
            href: adminPermissions.index().url,
        },
        {
            title: permission.name,
            href: adminPermissions.edit(permission.id).url,
        },
    ];

    const { data, setData, put, processing, errors } = useForm({
        name: permission.name,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(adminPermissions.update(permission.id).url);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${permission.name}`} />
            <div className="flex flex-1 flex-col gap-4 sm:gap-6 p-4">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">Edit Permission</h2>
                    <p className="text-muted-foreground">
                        Update permission information.
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
                                    required
                                />
                                {errors.name && (
                                    <p className="text-sm text-destructive">{errors.name}</p>
                                )}
                            </div>

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Save Changes
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
