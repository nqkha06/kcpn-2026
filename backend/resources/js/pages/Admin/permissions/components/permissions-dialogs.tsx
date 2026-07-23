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
import adminPermissions from '@/routes/admin/permissions';

interface Permission {
    id: number;
    name: string;
}

interface DeletePermissionDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    permission: Permission | null;
}

export function DeletePermissionDialog({
    open,
    onOpenChange,
    permission,
}: DeletePermissionDialogProps) {
    const { delete: destroy, processing } = useForm();

    const handleDelete = () => {
        if (permission) {
            destroy(adminPermissions.destroy(permission.id).url, {
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
                    <DialogTitle>Delete Permission</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete <strong>{permission?.name}</strong>? This
                        action cannot be undone.
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
