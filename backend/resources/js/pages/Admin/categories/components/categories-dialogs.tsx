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
import adminCategories from '@/routes/admin/categories';

interface Category {
    id: number;
    name: string;
}

interface DeleteCategoryDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    category: Category | null;
}

export function DeleteCategoryDialog({
    open,
    onOpenChange,
    category,
}: DeleteCategoryDialogProps) {
    const { delete: destroy, processing } = useForm();

    const handleDelete = () => {
        if (category) {
            destroy(adminCategories.destroy(category.id).url, {
                onSuccess: () => {
                    onOpenChange(false);
                },
            });
        }
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Delete Category</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete <strong>{category?.name}</strong>? This action cannot be undone.
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                        Cancel
                    </Button>
                    <Button type="button" variant="destructive" onClick={handleDelete} disabled={processing}>
                        Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
