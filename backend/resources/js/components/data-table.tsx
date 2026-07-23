import { router } from '@inertiajs/react';
import {
    ChevronDown,
    Search,
    SlidersHorizontal,
    X,
    Pencil,
    Trash2,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
    RotateCcw,
} from 'lucide-react';
import { useState, useEffect, useRef  } from 'react';
import type {ReactNode} from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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

import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';




const dateOnlyFormatter = new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
});

const dateTimeFormatter = new Intl.DateTimeFormat('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
});

const isDateLikeColumn = (columnId: string): boolean => {
    return /(date|time|_at|created|updated|expired|ngay)/i.test(columnId);
};

const isDateLikeValue = (value: string): boolean => {
    return /^\d{4}-\d{2}-\d{2}(?:[T\s]\d{2}:\d{2}(?::\d{2})?)?/.test(value);
};

const hasTimePart = (value: string): boolean => {
    return /[T\s]\d{2}:\d{2}/i.test(value);
};

const formatTableValue = (value: unknown, columnId: string): ReactNode => {
    if (value === null || value === undefined) {
        return '';
    }

    if (value instanceof Date) {
        return dateTimeFormatter.format(value);
    }

    if (typeof value === 'string') {
        const raw = value.trim();

        if (raw === '') {
            return '';
        }

        const shouldTryDateFormat = isDateLikeColumn(columnId) || isDateLikeValue(raw);

        if (shouldTryDateFormat) {
            const timestamp = Date.parse(raw);

            if (!Number.isNaN(timestamp)) {
                const date = new Date(timestamp);
                return hasTimePart(raw)
                    ? dateTimeFormatter.format(date)
                    : dateOnlyFormatter.format(date);
            }
        }

        return raw;
    }

    return value as ReactNode;
};

interface Column<T = any> {
    id: string;
    label: string;
    sortable?: boolean;
    render?: (row: T) => ReactNode;
}

interface AdvancedFilter {
    key: string;
    label: string;
    type: 'select' | 'input' | 'date';
    options?: { value: string; label: string }[];
    placeholder?: string;
}

interface DataTableProps<T = any> {
    data: T[];
    columns: Column<T>[];
    onEdit: (item: T) => void;
    onDelete: (item: T) => void;
    pagination: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: Record<string, any>;
    baseUrl: string;
    advancedFilters?: AdvancedFilter[];
}

export function DataTable<T>({
    data,
    columns,
    onEdit,
    onDelete,
    pagination,
    filters,
    baseUrl,
    advancedFilters,
}: DataTableProps<T>) {
    // Form state người dùng nhập → KHÔNG bao giờ mất
    const [formFilters, setFormFilters] = useState<Record<string, any>>({});
    const [search, setSearch] = useState('');
    const [showAdvancedFilters, setShowAdvancedFilters] = useState(false);
    const [visibleColumns, setVisibleColumns] = useState(columns.map((c) => c.id));

    const debounceRef = useRef<any>({});

    // Khi mount: sync formFilters từ URL + server
    // Intentionally run once on mount to hydrate filters from the URL
    const initialFiltersRef = useRef(filters);

    useEffect(() => {
        let params: Record<string, any> = {};

        if (typeof window !== 'undefined') {
            params = Object.fromEntries(
                new URLSearchParams(window.location.search).entries()
            );
        }

        // ưu tiên filter từ server
        const merged = { ...params, ...initialFiltersRef.current };

        setFormFilters(merged);
        setSearch(merged.search || '');
    }, []);

    // sanitize params trước khi push URL
    const sanitize = (raw: Record<string, any>) => {
        const cleaned: Record<string, any> = {};
        Object.entries(raw).forEach(([k, v]) => {
            if (v !== '' && v !== undefined && v !== null) cleaned[k] = v;
        });
        return cleaned;
    };

    // push filter vào URL
    const applyFilters = (updates: Record<string, any>) => {
        const merged = sanitize({
            ...formFilters,
            ...updates,
        });

        // reset page khi đổi filter
        if (!('page' in updates)) {
            delete merged.page;
        }

        router.get(
            `${baseUrl}?${new URLSearchParams(merged).toString()}`,
            {},
            { preserveState: true, preserveScroll: true }
        );
    };

    // Search logic
    const handleSearch = () => {
        const next = { ...formFilters, search };
        setFormFilters(next);
        applyFilters({ search, page: 1 });
    };

    const handleClearSearch = () => {
        setSearch('');
        setFormFilters((prev) => ({ ...prev, search: '' }));
        applyFilters({ search: '' });
    };

    // Debounce cho input nâng cao
    const handleDebounce = (key: string, value: any) => {
        if (debounceRef.current[key]) clearTimeout(debounceRef.current[key]);

        debounceRef.current[key] = setTimeout(() => {
            applyFilters({ [key]: value, page: 1 });
        }, 500);
    };

    // Sort
    const handleSort = (column: string) => {
        const cur = formFilters.sort ?? formFilters.sort_by;
        const dir = formFilters.dir ?? formFilters.sort_direction ?? 'asc';

        const nextDir = cur === column && dir === 'asc' ? 'desc' : 'asc';

        applyFilters({
            sort: column,
            dir: nextDir,
            sort_by: column,
            sort_direction: nextDir,
        });

        setFormFilters((prev) => ({
            ...prev,
            sort: column,
            dir: nextDir,
            sort_by: column,
            sort_direction: nextDir,
        }));
    };

    const getSortIcon = (col: string) => {
        const active = formFilters.sort ?? formFilters.sort_by;
        if (active !== col) return <ArrowUpDown className="ml-2 h-4 w-4" />;
        const dir = formFilters.dir ?? formFilters.sort_direction;
        return dir === 'asc' ? (
            <ArrowUp className="ml-2 h-4 w-4" />
        ) : (
            <ArrowDown className="ml-2 h-4 w-4" />
        );
    };

    const handleReset = () => {
        setSearch('');
        setFormFilters({});
        router.get(baseUrl, {}, { preserveState: true, preserveScroll: true });
    };

    const renderAdvanced = () => {
        if (!advancedFilters) return null;

        return (
            <div className="grid gap-4">
                <div className="grid gap-2 sm:grid-cols-2 md:grid-cols-3">
                    {advancedFilters.map((f) => (
                        <div key={f.key} className="space-y-2">
                            <label className="text-sm font-medium">{f.label}</label>

                            {f.type === 'select' && (
                                <Select
                                    value={formFilters[f.key] || ''}
                                    onValueChange={(v) => {
                                        setFormFilters((p) => ({ ...p, [f.key]: v }));
                                        applyFilters({ [f.key]: v, page: 1 });
                                    }}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder={f.placeholder || 'Select...'} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {f.options?.map((o) => (
                                            <SelectItem key={o.value} value={o.value}>
                                                {o.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            )}

                            {f.type === 'input' && (
                                <Input
                                    value={formFilters[f.key] || ''}
                                    placeholder={f.placeholder || ''}
                                    onChange={(e) => {
                                        const v = e.target.value;
                                        setFormFilters((p) => ({ ...p, [f.key]: v }));
                                        handleDebounce(f.key, v);
                                    }}
                                />
                            )}

                            {f.type === 'date' && (
                                <Input
                                    type="date"
                                    value={formFilters[f.key] || ''}
                                    onChange={(e) => {
                                        const v = e.target.value;
                                        setFormFilters((p) => ({ ...p, [f.key]: v }));
                                        applyFilters({ [f.key]: v, page: 1 });
                                    }}
                                />
                            )}
                        </div>
                    ))}
                </div>
            </div>
        );
    };

    return (
        <Card>
            <CardContent className="space-y-4 pt-6">
                {/* Search + Buttons */}
                <div className="flex flex-col sm:flex-row gap-2">
                    <div className="relative flex-1">
                        <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />

                        <Input
                            className="pl-8"
                            placeholder="Search..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && handleSearch()}
                        />

                        {search && (
                            <Button
                                variant="ghost"
                                size="icon"
                                className="absolute right-1 top-1 h-7 w-7"
                                onClick={handleClearSearch}
                            >
                                <X className="h-4 w-4" />
                            </Button>
                        )}
                    </div>

                    <Button onClick={handleSearch}>Search</Button>

                    <Button variant="outline" onClick={handleReset}>
                        <RotateCcw className="mr-2 h-4 w-4" /> Reset
                    </Button>

                    {advancedFilters && (
                        <Button
                            variant="outline"
                            size="icon"
                            onClick={() => setShowAdvancedFilters(!showAdvancedFilters)}
                        >
                            <SlidersHorizontal className="h-4 w-4" />
                        </Button>
                    )}

                    {/* Column visibility */}
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="outline" className="hidden md:flex">
                                Columns <ChevronDown className="ml-2 h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            {columns.map((col) => (
                                <DropdownMenuCheckboxItem
                                    key={col.id}
                                    checked={visibleColumns.includes(col.id)}
                                    onCheckedChange={() =>
                                        setVisibleColumns((prev) =>
                                            prev.includes(col.id)
                                                ? prev.filter((id) => id !== col.id)
                                                : [...prev, col.id]
                                        )
                                    }
                                >
                                    {col.label}
                                </DropdownMenuCheckboxItem>
                            ))}
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                {showAdvancedFilters && (
                    <div className="border rounded-lg p-4">{renderAdvanced()}</div>
                )}

                {/* Table */}
                <div className="rounded-md border overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                {columns
                                    .filter((c) => visibleColumns.includes(c.id))
                                    .map((c) => (
                                        <TableHead key={c.id}>
                                            {c.sortable ? (
                                                <Button
                                                    variant="ghost"
                                                    onClick={() => handleSort(c.id)}
                                                    className="-ml-4 h-8"
                                                >
                                                    {c.label}
                                                    {getSortIcon(c.id)}
                                                </Button>
                                            ) : (
                                                c.label
                                            )}
                                        </TableHead>
                                    ))}

                                <TableHead className="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            {data.length === 0 ? (
                                <TableRow>
                                    <TableCell
                                        colSpan={columns.length + 1}
                                        className="text-center text-muted-foreground"
                                    >
                                        No data found.
                                    </TableCell>
                                </TableRow>
                            ) : (
                                data.map((item, idx) => (
                                    <TableRow key={item.id}>
                                        {columns
                                            .filter((c) => visibleColumns.includes(c.id))
                                            .map((col) => (
                                                <TableCell key={`${col.id}-${idx}`} className={col.id === 'name' ? 'font-medium' : undefined}>
                                                    {col.render
                                                        ? col.render(item)
                                                        : formatTableValue(
                                                              (item as any)[col.id],
                                                              col.id
                                                          )}
                                                </TableCell>
                                            ))}

                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => onEdit(item)}
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    onClick={() => onDelete(item)}
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

                {/* Pagination */}
                <div className="flex flex-col sm:flex-row items-center justify-between gap-4 px-2">
                    <div className="text-sm text-muted-foreground">
                        Showing {pagination.from || 0} to {pagination.to || 0} of{' '}
                        {pagination.total}
                    </div>

                    <div className="flex items-center gap-4">
                        {/* Rows per page */}
                        <Select
                            value={String(pagination.per_page)}
                            onValueChange={(v) =>
                                applyFilters({ per_page: v, page: 1 })
                            }
                        >
                            <SelectTrigger className="h-8 w-[70px]">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="10">10</SelectItem>
                                <SelectItem value="20">20</SelectItem>
                                <SelectItem value="50">50</SelectItem>
                                <SelectItem value="100">100</SelectItem>
                            </SelectContent>
                        </Select>

                        {/* Page */}
                        <div className="flex items-center gap-1">
                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8 hidden sm:flex"
                                onClick={() => applyFilters({ page: 1 })}
                                disabled={pagination.current_page === 1}
                            >
                                <ChevronsLeft className="h-4 w-4" />
                            </Button>

                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8"
                                onClick={() =>
                                    applyFilters({
                                        page: pagination.current_page - 1,
                                    })
                                }
                                disabled={pagination.current_page === 1}
                            >
                                <ChevronLeft className="h-4 w-4" />
                            </Button>

                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8"
                                onClick={() =>
                                    applyFilters({
                                        page: pagination.current_page + 1,
                                    })
                                }
                                disabled={
                                    pagination.current_page === pagination.last_page
                                }
                            >
                                <ChevronRight className="h-4 w-4" />
                            </Button>

                            <Button
                                variant="outline"
                                size="icon"
                                className="h-8 w-8 hidden sm:flex"
                                onClick={() =>
                                    applyFilters({
                                        page: pagination.last_page,
                                    })
                                }
                                disabled={
                                    pagination.current_page === pagination.last_page
                                }
                            >
                                <ChevronsRight className="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
