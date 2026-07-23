import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface UsersPrimaryButtonsProps {
    onAdd: () => void;
}

export function UsersPrimaryButtons({ onAdd }: UsersPrimaryButtonsProps) {
    return (
        <Button onClick={onAdd}>
            <Plus className="mr-2 h-4 w-4" />
            Add User
        </Button>
    );
}
