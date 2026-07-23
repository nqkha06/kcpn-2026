import { Head, router } from '@inertiajs/react'
import { useState } from 'react'
import { DataTable } from '@/components/data-table'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import AdminLayout from '@/layouts/admin'
import adminLanguages from '@/routes/admin/settings/languages'
import type {BreadcrumbItem} from '@/types';

interface Language {
    id: number
    name: string
    locale: string
    code: string
    flag?: string | null
    is_default: boolean
    is_rtl: boolean
    order: number
}

interface Pagination {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
}

interface Props {
    languages: Language[]
    pagination: Pagination
    filters: Record<string, any>
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Languages',
        href: adminLanguages.index().url,
    },
]

export default function LanguageList({ languages = [], pagination, filters }: Props) {
    const [deletingId, setDeletingId] = useState<number | null>(null)

    const columns = [
        { id: 'name', label: 'Name', sortable: true },
        { id: 'locale', label: 'Locale', sortable: true },
        { id: 'code', label: 'Code', sortable: true },
        {
            id: 'is_default',
            label: 'Default',
            sortable: true,
            render: (lang: Language) => (
                <Badge variant={lang.is_default ? 'default' : 'secondary'}>
                    {lang.is_default ? 'Default' : 'No'}
                </Badge>
            ),
        },
        {
            id: 'is_rtl',
            label: 'RTL',
            sortable: true,
            render: (lang: Language) => (
                <Badge variant={lang.is_rtl ? 'default' : 'secondary'}>
                    {lang.is_rtl ? 'RTL' : 'LTR'}
                </Badge>
            ),
        },
        {
            id: 'order',
            label: 'Order',
            sortable: true,
        },
    ]

    const handleAdd = () => router.visit(adminLanguages.create().url)
    const handleEdit = (lang: Language) => router.visit(adminLanguages.edit(lang.id).url)
    const handleDelete = (lang: Language) => {
        if (deletingId) return
        if (!confirm(`Delete language "${lang.name}"?`)) return
        setDeletingId(lang.id)
        router.delete(adminLanguages.destroy(lang.id).url, {
            onFinish: () => setDeletingId(null),
        })
    }

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={breadcrumbs[0].title} />
            <div className="flex flex-1 flex-col gap-4 sm:gap-6 p-4">
                <div className="flex flex-wrap items-end justify-between gap-2">
                    <div>
                        <h2 className="text-2xl font-bold tracking-tight">Languages</h2>
                        <p className="text-muted-foreground">
                            Manage available languages and default locale.
                        </p>
                    </div>
                    <Button onClick={handleAdd}>Add Language</Button>
                </div>

                <DataTable<Language>
                    data={languages}
                    columns={columns}
                    onEdit={handleEdit}
                    onDelete={handleDelete}
                    pagination={pagination}
                    filters={filters}
                    baseUrl={adminLanguages.index().url}
                    advancedFilters={[
                        {
                            key: 'is_default', label: 'Default', type: 'select', options: [
                                { value: '1', label: 'Default' },
                                { value: '0', label: 'Non-default' },
                            ]
                        },
                        {
                            key: 'is_rtl', label: 'RTL', type: 'select', options: [
                                { value: '1', label: 'RTL' },
                                { value: '0', label: 'LTR' },
                            ]
                        },
                        { key: 'search', label: 'Search', type: 'input', placeholder: 'Name/locale/code' },
                    ]}
                />
            </div>
        </AdminLayout>
    )
}
