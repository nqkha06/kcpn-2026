import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import adminTransactions from '@/routes/admin/transactions';

interface Transaction {
    id: number;
    note?: string | null;
    amount: number;
}

interface DeleteTransactionDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    transaction: Transaction | null;
}

export function DeleteTransactionDialog({
    open,
    onOpenChange,
    transaction,
}: DeleteTransactionDialogProps) {
    const { delete: destroy, processing } = useForm();

    const handleDelete = () => {
        if (transaction === null) {
            return;
        }

        destroy(adminTransactions.destroy(transaction.id).url, {
            onSuccess: () => {
                onOpenChange(false);
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Transaction</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete transaction{' '}
                        <strong>
                            {transaction?.note ??
                                `$${transaction?.amount ?? 0}`}
                        </strong>
                        ? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                    >
                        Cancel
                    </Button>
                    <Button
                        type="button"
                        variant="destructive"
                        onClick={handleDelete}
                        disabled={processing}
                    >
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
