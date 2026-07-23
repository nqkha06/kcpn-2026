import { Head, useForm } from '@inertiajs/react';
import type { FormEventHandler } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin';
import adminUsers from '@/routes/admin/users';
import type {BreadcrumbItem, User} from '@/types';

interface Role {
    id: number;
    name: string;
}

interface Props {
    user: User & { roles?: Role[] };
    roles?: Role[];
}

export default function EditUser({ user, roles = [] }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Users',
            href: adminUsers.index().url,
        },
        {
            title: user.name,
            href: adminUsers.edit(user.id).url,
        },
    ];

    const assignedRoleIds = user.roles?.map(r => r.id) || [];

    const { data, setData, put, processing, errors } = useForm({
        name: user.name,
        email: user.email,
        password: '',
        password_confirmation: '',
        roles: assignedRoleIds as number[],
    });

    const handleRoleToggle = (roleId: number, checked: boolean) => {
        if (checked) {
            setData('roles', [...data.roles, roleId]);
        } else {
            setData('roles', data.roles.filter(id => id !== roleId));
        }
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(adminUsers.update(user.id).url);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${user.name}`} />
            <div className="flex flex-1 flex-col gap-4 sm:gap-6 p-4">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">Edit User</h2>
                    <p className="text-muted-foreground">
                        Update user information.
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>User Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
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
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    required
                                />
                                {errors.email && (
                                    <p className="text-sm text-destructive">{errors.email}</p>
                                )}
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    Password (leave blank to keep current)
                                </Label>
                                <Input
                                    id="password"
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                />
                                {errors.password && (
                                    <p className="text-sm text-destructive">{errors.password}</p>
                                )}
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password_confirmation">Confirm Password</Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    value={data.password_confirmation}
                                    onChange={(e) =>
                                        setData('password_confirmation', e.target.value)
                                    }
                                />
                            </div>

                            <div className="grid gap-2">
                                <Label>Assign Roles</Label>
                                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mt-1">
                                    {roles.map((role) => (
                                        <div key={role.id} className="flex items-center space-x-2 border rounded-lg p-3">
                                            <Checkbox
                                                id={`role-${role.id}`}
                                                checked={data.roles.includes(role.id)}
                                                onCheckedChange={(checked) => handleRoleToggle(role.id, checked as boolean)}
                                            />
                                            <Label htmlFor={`role-${role.id}`} className="font-normal cursor-pointer flex-1">
                                                {role.name}
                                            </Label>
                                        </div>
                                    ))}
                                    {roles.length === 0 && (
                                        <p className="text-sm text-muted-foreground">No roles available.</p>
                                    )}
                                </div>
                            </div>

                            <div className="flex gap-2">
                                <Button type="submit" disabled={processing}>
                                    Update User
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
