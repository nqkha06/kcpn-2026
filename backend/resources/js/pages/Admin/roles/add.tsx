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

interface Props {
    permissions: Permission[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Roles',
        href: adminRoles.index().url,
    },
    {
        title: 'Create',
        href: adminRoles.create().url,
    },
];

export default function RoleAdd({ permissions = [] }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        permissions: [] as number[],
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
        post(adminRoles.store().url);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Role" />
            <div className="flex flex-1 flex-col gap-4 sm:gap-6 p-4">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">Create Role</h2>
                    <p className="text-muted-foreground">
                        Add a new role to the system.
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
                                    placeholder="e.g. Editor"
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
                                            No permissions available. Please create permissions first.
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Create Role
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
