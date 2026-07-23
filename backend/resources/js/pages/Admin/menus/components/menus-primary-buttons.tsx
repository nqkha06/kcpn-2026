import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface MenusPrimaryButtonsProps {
    onAdd: () => void;
}

export function MenusPrimaryButtons({ onAdd }: MenusPrimaryButtonsProps) {
    return (
        <Button onClick={onAdd} className="inline-flex items-center gap-2">
            <Plus className="h-4 w-4" />
            Add Menu
        </Button>
    );
}
