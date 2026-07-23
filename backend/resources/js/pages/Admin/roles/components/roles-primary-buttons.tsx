import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface RolesPrimaryButtonsProps {
    onAdd: () => void;
}

export function RolesPrimaryButtons({ onAdd }: RolesPrimaryButtonsProps) {
    return (
        <Button onClick={onAdd}>
            <Plus className="mr-2 h-4 w-4" />
            Add Role
        </Button>
    );
}
