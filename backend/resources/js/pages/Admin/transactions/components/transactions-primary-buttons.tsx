import { Plus } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface TransactionsPrimaryButtonsProps {
    onAdd: () => void;
}

export function TransactionsPrimaryButtons({
    onAdd,
}: TransactionsPrimaryButtonsProps) {
    return (
        <Button type="button" onClick={onAdd}>
            <Plus className="mr-2 h-4 w-4" />
            Add Transaction
        </Button>
    );
}
