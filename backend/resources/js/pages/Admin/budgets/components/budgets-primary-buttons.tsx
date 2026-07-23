import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface BudgetsPrimaryButtonsProps {
    onAdd: () => void;
}

export function BudgetsPrimaryButtons({ onAdd }: BudgetsPrimaryButtonsProps) {
    return (
        <Button type="button" onClick={onAdd}>
            <Plus className="mr-2 h-4 w-4" />
            Add Budget
        </Button>
    );
}
