import { Head, useForm } from '@inertiajs/react';
import type { FormEventHandler } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin';
import adminBudgets from '@/routes/admin/budgets';
import type { BreadcrumbItem } from '@/types';

interface Budget {
    id: number;
    user_id: number;
    category_id: number;
    amount_limit: number;
    period: 'monthly' | 'yearly';
    status: 'active' | 'inactive';
    note: string | null;
}

interface FormOptions {
    users: Array<{
        id: number;
        name: string;
        email: string;
    }>;
    categories: Array<{
        id: number;
        name: string;
        color: string;
    }>;
}

interface Props {
    budget: Budget;
    formOptions: FormOptions;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Budgets',
        href: adminBudgets.index().url,
    },
    {
        title: 'Edit',
        href: '#',
    },
];

export default function BudgetEdit({ budget, formOptions }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        user_id: String(budget.user_id),
        category_id: String(budget.category_id),
        amount_limit: String(budget.amount_limit),
        period: budget.period,
        status: budget.status,
        note: budget.note ?? '',
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        put(adminBudgets.update(budget.id).url);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Budget" />
            <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">Edit Budget</h2>
                    <p className="text-muted-foreground">
                        Update budget details and status.
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Budget Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="user_id">User</Label>
                                    <Select
                                        value={data.user_id}
                                        onValueChange={(value) => setData('user_id', value)}
                                    >
                                        <SelectTrigger id="user_id">
                                            <SelectValue placeholder="Select user" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {formOptions.users.map((user) => (
                                                <SelectItem key={user.id} value={String(user.id)}>
                                                    {user.name} ({user.email})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.user_id ? (
                                        <p className="text-sm text-destructive">{errors.user_id}</p>
                                    ) : null}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="category_id">Category</Label>
                                    <Select
                                        value={data.category_id}
                                        onValueChange={(value) => setData('category_id', value)}
                                    >
                                        <SelectTrigger id="category_id">
                                            <SelectValue placeholder="Select category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {formOptions.categories.map((category) => (
                                                <SelectItem key={category.id} value={String(category.id)}>
                                                    {category.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.category_id ? (
                                        <p className="text-sm text-destructive">{errors.category_id}</p>
                                    ) : null}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div className="grid gap-2">
                                    <Label htmlFor="amount_limit">Limit</Label>
                                    <Input
                                        id="amount_limit"
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        value={data.amount_limit}
                                        onChange={(event) => setData('amount_limit', event.target.value)}
                                        placeholder="0.00"
                                    />
                                    {errors.amount_limit ? (
                                        <p className="text-sm text-destructive">{errors.amount_limit}</p>
                                    ) : null}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="period">Period</Label>
                                    <Select
                                        value={data.period}
                                        onValueChange={(value) => setData('period', value as 'monthly' | 'yearly')}
                                    >
                                        <SelectTrigger id="period">
                                            <SelectValue placeholder="Select period" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="monthly">Monthly</SelectItem>
                                            <SelectItem value="yearly">Yearly</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.period ? (
                                        <p className="text-sm text-destructive">{errors.period}</p>
                                    ) : null}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="status">Status</Label>
                                    <Select
                                        value={data.status}
                                        onValueChange={(value) => setData('status', value as 'active' | 'inactive')}
                                    >
                                        <SelectTrigger id="status">
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="active">Active</SelectItem>
                                            <SelectItem value="inactive">Inactive</SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.status ? (
                                        <p className="text-sm text-destructive">{errors.status}</p>
                                    ) : null}
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="note">Note</Label>
                                <Textarea
                                    id="note"
                                    value={data.note}
                                    onChange={(event) => setData('note', event.target.value)}
                                    placeholder="Optional budget note"
                                />
                                {errors.note ? (
                                    <p className="text-sm text-destructive">{errors.note}</p>
                                ) : null}
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
