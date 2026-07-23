import { Head, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
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
import adminTransactions from '@/routes/admin/transactions';
import type { BreadcrumbItem } from '@/types';

interface Transaction {
    id: number;
    user_id: number;
    wallet_id: number;
    category_id: number | null;
    type: 'income' | 'expense';
    amount: number;
    status: 'posted' | 'pending' | 'cancelled';
    note: string | null;
    labels: string[];
    transacted_at: string | null;
}

interface FormOptions {
    users: Array<{
        id: number;
        name: string;
        email: string;
    }>;
    wallets: Array<{
        id: number;
        user_id: number;
        name: string;
        currency: string;
        user_name?: string | null;
    }>;
    categories: Array<{
        id: number;
        name: string;
        color: string;
        status: string;
    }>;
}

interface Props {
    transaction: Transaction;
    formOptions: FormOptions;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transactions',
        href: adminTransactions.index().url,
    },
    {
        title: 'Edit',
        href: '#',
    },
];

export default function TransactionEdit({ transaction, formOptions }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        user_id: String(transaction.user_id),
        wallet_id: String(transaction.wallet_id),
        category_id:
            transaction.category_id === null
                ? 'none'
                : String(transaction.category_id),
        type: transaction.type,
        amount: String(transaction.amount),
        transacted_at:
            transaction.transacted_at ?? new Date().toISOString().slice(0, 10),
        status: transaction.status,
        note: transaction.note ?? '',
        labels: transaction.labels.join(', '),
    });

    const filteredWallets = formOptions.wallets.filter((wallet) => {
        if (data.user_id === '') {
            return true;
        }

        return String(wallet.user_id) === String(data.user_id);
    });

    useEffect(() => {
        if (filteredWallets.length === 0) {
            if (data.wallet_id !== '') {
                setData('wallet_id', '');
            }

            return;
        }

        const selectedWalletExists = filteredWallets.some(
            (wallet) => String(wallet.id) === String(data.wallet_id),
        );

        if (!selectedWalletExists) {
            setData('wallet_id', String(filteredWallets[0].id));
        }
    }, [data.user_id, data.wallet_id, filteredWallets, setData]);

    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        put(adminTransactions.update(transaction.id).url);
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title="Edit Transaction" />
            <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">
                        Edit Transaction
                    </h2>
                    <p className="text-muted-foreground">
                        Update transaction details and status.
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Transaction Details</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="user_id">User</Label>
                                    <Select
                                        value={data.user_id}
                                        onValueChange={(value) =>
                                            setData('user_id', value)
                                        }
                                    >
                                        <SelectTrigger id="user_id">
                                            <SelectValue placeholder="Select user" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {formOptions.users.map((user) => (
                                                <SelectItem
                                                    key={user.id}
                                                    value={String(user.id)}
                                                >
                                                    {user.name} ({user.email})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.user_id ? (
                                        <p className="text-sm text-destructive">
                                            {errors.user_id}
                                        </p>
                                    ) : null}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="wallet_id">Wallet</Label>
                                    <Select
                                        value={data.wallet_id}
                                        onValueChange={(value) =>
                                            setData('wallet_id', value)
                                        }
                                    >
                                        <SelectTrigger id="wallet_id">
                                            <SelectValue placeholder="Select wallet" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {filteredWallets.map((wallet) => (
                                                <SelectItem
                                                    key={wallet.id}
                                                    value={String(wallet.id)}
                                                >
                                                    {wallet.name} (
                                                    {wallet.currency})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors.wallet_id ? (
                                        <p className="text-sm text-destructive">
                                            {errors.wallet_id}
                                        </p>
                                    ) : null}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                                <div className="grid gap-2">
                                    <Label htmlFor="type">Type</Label>
                                    <Select
                                        value={data.type}
                                        onValueChange={(value) =>
                                            setData('type', value)
                                        }
                                    >
                                        <SelectTrigger id="type">
                                            <SelectValue placeholder="Select type" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="income">
                                                Income
                                            </SelectItem>
                                            <SelectItem value="expense">
                                                Expense
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.type ? (
                                        <p className="text-sm text-destructive">
                                            {errors.type}
                                        </p>
                                    ) : null}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="amount">Amount</Label>
                                    <Input
                                        id="amount"
                                        type="number"
                                        min="0.01"
                                        step="0.01"
                                        value={data.amount}
                                        onChange={(event) =>
                                            setData(
                                                'amount',
                                                event.target.value,
                                            )
                                        }
                                        placeholder="0.00"
                                    />
                                    {errors.amount ? (
                                        <p className="text-sm text-destructive">
                                            {errors.amount}
                                        </p>
                                    ) : null}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="transacted_at">
                                        Transaction Date
                                    </Label>
                                    <Input
                                        id="transacted_at"
                                        type="date"
                                        value={data.transacted_at}
                                        onChange={(event) =>
                                            setData(
                                                'transacted_at',
                                                event.target.value,
                                            )
                                        }
                                    />
                                    {errors.transacted_at ? (
                                        <p className="text-sm text-destructive">
                                            {errors.transacted_at}
                                        </p>
                                    ) : null}
                                </div>
                            </div>

                            <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="category_id">
                                        Category
                                    </Label>
                                    <Select
                                        value={data.category_id}
                                        onValueChange={(value) =>
                                            setData('category_id', value)
                                        }
                                    >
                                        <SelectTrigger id="category_id">
                                            <SelectValue placeholder="Select category" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="none">
                                                No category
                                            </SelectItem>
                                            {formOptions.categories.map(
                                                (category) => (
                                                    <SelectItem
                                                        key={category.id}
                                                        value={String(
                                                            category.id,
                                                        )}
                                                    >
                                                        {category.name}
                                                    </SelectItem>
                                                ),
                                            )}
                                        </SelectContent>
                                    </Select>
                                    {errors.category_id ? (
                                        <p className="text-sm text-destructive">
                                            {errors.category_id}
                                        </p>
                                    ) : null}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="status">Status</Label>
                                    <Select
                                        value={data.status}
                                        onValueChange={(value) =>
                                            setData('status', value)
                                        }
                                    >
                                        <SelectTrigger id="status">
                                            <SelectValue placeholder="Select status" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="posted">
                                                Posted
                                            </SelectItem>
                                            <SelectItem value="pending">
                                                Pending
                                            </SelectItem>
                                            <SelectItem value="cancelled">
                                                Cancelled
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {errors.status ? (
                                        <p className="text-sm text-destructive">
                                            {errors.status}
                                        </p>
                                    ) : null}
                                </div>
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="note">Note</Label>
                                <Textarea
                                    id="note"
                                    value={data.note}
                                    onChange={(event) =>
                                        setData('note', event.target.value)
                                    }
                                    placeholder="Optional transaction note"
                                />
                                {errors.note ? (
                                    <p className="text-sm text-destructive">
                                        {errors.note}
                                    </p>
                                ) : null}
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="labels">
                                    Labels (comma separated)
                                </Label>
                                <Input
                                    id="labels"
                                    value={data.labels}
                                    onChange={(event) =>
                                        setData('labels', event.target.value)
                                    }
                                    placeholder="salary, monthly"
                                />
                                {errors.labels ? (
                                    <p className="text-sm text-destructive">
                                        {errors.labels}
                                    </p>
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
