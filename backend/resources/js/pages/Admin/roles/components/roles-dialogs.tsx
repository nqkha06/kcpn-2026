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
import adminRoles from '@/routes/admin/roles';

interface Role {
    id: number;
    name: string;
}

interface DeleteRoleDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    role: Role | null;
}

export function DeleteRoleDialog({
    open,
    onOpenChange,
    role,
}: DeleteRoleDialogProps) {
    const { delete: destroy, processing } = useForm();

    const handleDelete = () => {
        if (role) {
            destroy(adminRoles.destroy(role.id).url, {
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
                    <DialogTitle>Delete Role</DialogTitle>
                    <DialogDescription>
                        Are you sure you want to delete <strong>{role?.name}</strong>? This
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
