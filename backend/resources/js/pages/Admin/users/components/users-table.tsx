import { Pencil, Trash2, ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type {User} from '@/types';

interface UsersTableProps {
    users: User[];
    onEdit: (user: User) => void;
    onDelete: (user: User) => void;
    sortColumn: string | null;
    sortDirection: 'asc' | 'desc';
    onSort: (column: string) => void;
    visibleColumns: string[];
}

const COLUMNS = [
    { id: 'name', label: 'Name', sortable: true },
    { id: 'email', label: 'Email', sortable: true },
    { id: 'created_at', label: 'Created At', sortable: true },
];

export function UsersTable({
    users,
    onEdit,
    onDelete,
    sortColumn,
    sortDirection,
    onSort,
    visibleColumns,
}: UsersTableProps) {
    const getSortIcon = (columnId: string) => {
        if (sortColumn !== columnId) {
            return <ArrowUpDown className="ml-2 h-4 w-4" />;
        }
        return sortDirection === 'asc' ? (
            <ArrowUp className="ml-2 h-4 w-4" />
        ) : (
            <ArrowDown className="ml-2 h-4 w-4" />
        );
    };

    return (
        <div className="rounded-md border">
            <Table>
                <TableHeader>
                    <TableRow>
                        {COLUMNS.filter((col) => visibleColumns.includes(col.id)).map((column) => (
                            <TableHead key={column.id}>
                                {column.sortable ? (
                                    <Button
                                        variant="ghost"
                                        onClick={() => onSort(column.id)}
                                        className="-ml-4 h-8"
                                    >
                                        {column.label}
                                        {getSortIcon(column.id)}
                                    </Button>
                                ) : (
                                    column.label
                                )}
                            </TableHead>
                        ))}
                        <TableHead className="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    {users.length === 0 ? (
                        <TableRow>
                            <TableCell
                                colSpan={visibleColumns.length + 1}
                                className="text-center text-muted-foreground"
                            >
                                No users found.
                            </TableCell>
                        </TableRow>
                    ) : (
                        users.map((user) => (
                            <TableRow key={user.id}>
                                {visibleColumns.includes('name') && (
                                    <TableCell className="font-medium">{user.name}</TableCell>
                                )}
                                {visibleColumns.includes('email') && (
                                    <TableCell>{user.email}</TableCell>
                                )}
                                {visibleColumns.includes('created_at') && (
                                    <TableCell>
                                        {new Date(user.created_at).toLocaleDateString()}
                                    </TableCell>
                                )}
                                <TableCell className="text-right">
                                    <div className="flex justify-end gap-2">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => onEdit(user)}
                                        >
                                            <Pencil className="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => onDelete(user)}
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        ))
                    )}
                </TableBody>
            </Table>
        </div>
    );
}
