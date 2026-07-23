import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface PagesPrimaryButtonsProps {
    onAdd: () => void;
}

export function PagesPrimaryButtons({ onAdd }: PagesPrimaryButtonsProps) {
    return (
        <Button onClick={onAdd}>
            <Plus className="mr-2 h-4 w-4" />
            Add Page
        </Button>
    );
}
