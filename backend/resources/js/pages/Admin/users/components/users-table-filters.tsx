import { ChevronDown, Search, SlidersHorizontal, X } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

interface Column {
    id: string;
    label: string;
}

interface UsersTableFiltersProps {
    search: string;
    onSearchChange: (value: string) => void;
    columns: Column[];
    visibleColumns: string[];
    onColumnVisibilityChange: (columnId: string) => void;
    onAdvancedFilterToggle: () => void;
    showAdvancedFilters: boolean;
}

export function UsersTableFilters({
    search,
    onSearchChange,
    columns,
    visibleColumns,
    onColumnVisibilityChange,
    onAdvancedFilterToggle,
    showAdvancedFilters,
}: UsersTableFiltersProps) {
    return (
        <div className="space-y-4">
            <div className="flex items-center gap-2">
                <div className="relative flex-1">
                    <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                        placeholder="Search users..."
                        value={search}
                        onChange={(e) => onSearchChange(e.target.value)}
                        className="pl-8"
                    />
                    {search && (
                        <Button
                            variant="ghost"
                            size="icon"
                            className="absolute right-1 top-1 h-7 w-7"
                            onClick={() => onSearchChange('')}
                        >
                            <X className="h-4 w-4" />
                        </Button>
                    )}
                </div>

                <Button
                    variant="outline"
                    size="icon"
                    onClick={onAdvancedFilterToggle}
                    className={showAdvancedFilters ? 'bg-accent' : ''}
                >
                    <SlidersHorizontal className="h-4 w-4" />
                </Button>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="outline">
                            Columns <ChevronDown className="ml-2 h-4 w-4" />
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        {columns.map((column) => (
                            <DropdownMenuCheckboxItem
                                key={column.id}
                                checked={visibleColumns.includes(column.id)}
                                onCheckedChange={() => onColumnVisibilityChange(column.id)}
                            >
                                {column.label}
                            </DropdownMenuCheckboxItem>
                        ))}
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>

            {showAdvancedFilters && (
                <div className="grid gap-4 rounded-lg border p-4">
                    <div className="grid gap-2 sm:grid-cols-2 md:grid-cols-3">
                        <div className="space-y-2">
                            <label className="text-sm font-medium">Email Verified</label>
                            <Select>
                                <SelectTrigger>
                                    <SelectValue placeholder="All" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All</SelectItem>
                                    <SelectItem value="verified">Verified</SelectItem>
                                    <SelectItem value="unverified">Unverified</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <label className="text-sm font-medium">Status</label>
                            <Select>
                                <SelectTrigger>
                                    <SelectValue placeholder="All" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All</SelectItem>
                                    <SelectItem value="active">Active</SelectItem>
                                    <SelectItem value="inactive">Inactive</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <label className="text-sm font-medium">Created Date</label>
                            <Select>
                                <SelectTrigger>
                                    <SelectValue placeholder="All time" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All time</SelectItem>
                                    <SelectItem value="today">Today</SelectItem>
                                    <SelectItem value="week">This week</SelectItem>
                                    <SelectItem value="month">This month</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
