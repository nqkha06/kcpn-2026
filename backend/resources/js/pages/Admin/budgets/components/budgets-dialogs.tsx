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
import adminBudgets from '@/routes/admin/budgets';

interface Budget {
    id: number;
    user?: {
        name: string;
    } | null;
    category?: {
        name: string;
    } | null;
}

interface DeleteBudgetDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    budget: Budget | null;
}

export function DeleteBudgetDialog({
    open,
    onOpenChange,
    budget,
}: DeleteBudgetDialogProps) {
    const { delete: destroy, processing } = useForm();

    const handleDelete = () => {
        if (budget === null) {
            return;
        }

        destroy(adminBudgets.destroy(budget.id).url, {
            onSuccess: () => {
                onOpenChange(false);
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Budget</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete budget{' '}
                        <strong>
                            {budget?.user?.name ?? 'User'} - {budget?.category?.name ?? 'Category'}
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
