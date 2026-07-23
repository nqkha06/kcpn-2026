import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface CategoriesPrimaryButtonsProps {
    onAdd: () => void;
}

export function CategoriesPrimaryButtons({ onAdd }: CategoriesPrimaryButtonsProps) {
    return (
        <div className="flex items-center gap-2">
            <Button type="button" onClick={onAdd}>
                <Plus className="mr-2 h-4 w-4" />
                Add Category
            </Button>
        </div>
    );
}
