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
import adminMenus from '@/routes/admin/menus';

interface MenuItem {
    id: number;
    title: string;
}

interface DeleteMenuDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    menu: MenuItem | null;
}

export function DeleteMenuDialog({
    open,
    onOpenChange,
    menu,
}: DeleteMenuDialogProps) {
    const { delete: destroy, processing } = useForm();

    const handleDelete = () => {
        if (menu) {
            destroy(adminMenus.destroy(menu.id).url, {
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
                    <DialogTitle>Delete Menu</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete <strong>{menu?.title}</strong>? Sub menus will be detached.
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
