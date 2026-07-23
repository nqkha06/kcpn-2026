import { Head, useForm } from '@inertiajs/react';
import type { FormEventHandler } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin';
import adminRoles from '@/routes/admin/roles';
import type {BreadcrumbItem} from '@/types';

interface Permission {
    id: number;
    name: string;
}

interface Role {
    id: number;
    name: string;
    permissions: Permission[];
}

interface Props {
    role: Role;
    permissions: Permission[];
}

export default function RoleEdit({ role, permissions = [] }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Roles',
            href: adminRoles.index().url,
        },
        {
            title: role.name,
            href: adminRoles.edit(role.id).url,
        },
    ];

    const assignedPermissionIds = role.permissions?.map((p) => p.id) || [];

    const { data, setData, put, processing, errors } = useForm({
        name: role.name,
        permissions: assignedPermissionIds as number[],
    });

    const handlePermissionToggle = (permissionId: number, checked: boolean) => {
        if (checked) {
            setData('permissions', [...data.permissions, permissionId]);
        } else {
            setData('permissions', data.permissions.filter((id) => id !== permissionId));
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(adminRoles.update(role.id).url);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${role.name}`} />
            <div className="flex flex-1 flex-col gap-4 sm:gap-6 p-4">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">Edit Role</h2>
                    <p className="text-muted-foreground">
                        Update role information and permissions.
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Role Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-6">
                            <div className="grid gap-2 max-w-xl">
                                <Label htmlFor="name">Role Name</Label>
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

                            <div className="grid gap-2">
                                <Label>Assign Permissions</Label>
                                {errors.permissions && (
                                    <p className="text-sm text-destructive">{errors.permissions}</p>
                                )}
                                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mt-1">
                                    {permissions.map((permission) => (
                                        <div
                                            key={permission.id}
                                            className="flex items-center space-x-2 border rounded-lg p-3"
                                        >
                                            <Checkbox
                                                id={`permission-${permission.id}`}
                                                checked={data.permissions.includes(permission.id)}
                                                onCheckedChange={(checked) =>
                                                    handlePermissionToggle(permission.id, checked as boolean)
                                                }
                                            />
                                            <Label
                                                htmlFor={`permission-${permission.id}`}
                                                className="font-normal cursor-pointer flex-1"
                                            >
                                                {permission.name}
                                            </Label>
                                        </div>
                                    ))}
                                    {permissions.length === 0 && (
                                        <p className="text-sm text-muted-foreground">
                                            No permissions available.
                                        </p>
                                    )}
                                </div>
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
