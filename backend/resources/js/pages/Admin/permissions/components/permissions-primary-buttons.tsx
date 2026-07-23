import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface PermissionsPrimaryButtonsProps {
    onAdd: () => void;
}

export function PermissionsPrimaryButtons({ onAdd }: PermissionsPrimaryButtonsProps) {
    return (
        <Button onClick={onAdd}>
            <Plus className="mr-2 h-4 w-4" />
            Add Permission
        </Button>
    );
}
