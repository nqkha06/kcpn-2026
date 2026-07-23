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
import adminPages from '@/routes/admin/pages';

interface PageItem {
    id: number;
    title?: string;
}

interface DeletePageDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    page: PageItem | null;
}

export function DeletePageDialog({
    open,
    onOpenChange,
    page,
}: DeletePageDialogProps) {
    const { delete: destroy, processing } = useForm();

    const handleDelete = () => {
        if (page) {
            destroy(adminPages.destroy(page.id).url, {
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
                    <DialogTitle>Delete Page</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete <strong>{page?.title || 'this page'}</strong>? This action cannot be undone.
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
