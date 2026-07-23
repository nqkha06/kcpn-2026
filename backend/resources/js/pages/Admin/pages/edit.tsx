import { Head, router, useForm } from '@inertiajs/react';
import { useMemo } from 'react';
import type { FormEventHandler } from 'react';
import RichTextEditor from '@/components/editor/rich-text-editor';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin';
import adminPages from '@/routes/admin/pages';
import type { BreadcrumbItem } from '@/types';

interface PagePayload {
    id: number;
    title?: string;
    slug?: string;
    image?: string | null;
    content?: string | null;
    meta_title?: string | null;
    meta_description?: string | null;
    meta_keywords?: string | null;
    category_id?: number | null;
    tags?: string[] | string | null;
    status?: string;
}

interface Props {
    page: PagePayload;
}

const STATUS_OPTIONS = [
    { value: 'published', label: 'Published' },
    { value: 'draft', label: 'Draft' },
    { value: 'pending', label: 'Pending' },
];

export default function EditPage({ page }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Pages', href: adminPages.index().url },
        { title: 'Edit', href: adminPages.edit(page.id).url },
    ];

    const initialTags = useMemo(() => {
        if (Array.isArray(page.tags)) {
            return page.tags.join(', ');
        }

        if (typeof page.tags === 'string') {
            return page.tags;
        }

        return '';
    }, [page.tags]);

    const { data, setData, put, processing, errors } = useForm({
        status: page.status || 'draft',
        title: page.title || '',
        slug: page.slug || '',
        image: page.image || '',
        content: page.content || '',
        meta_title: page.meta_title || '',
        meta_description: page.meta_description || '',
        meta_keywords: page.meta_keywords || '',
        tags: initialTags,
    });

    const permalinkPreview = useMemo(() => {
        const slug = data.slug?.trim() || page.slug?.trim() || '';
        const baseUrl = typeof window !== 'undefined' ? window.location.origin : '';

        if (slug === '' || baseUrl === '') {
            return null;
        }

        return `${baseUrl}/${slug}`;
    }, [data.slug, page.slug]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(adminPages.update(page.id).url);
    };

    const submitAndExit = () => {
        put(adminPages.update(page.id).url, {
            onSuccess: () => {
                router.visit(adminPages.index().url);
            },
        });
    };

    return (
        <AdminLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Page #${page.id}`} />
            <div className="flex flex-1 flex-col gap-4 p-4 sm:gap-6">
                <div>
                    <h2 className="text-2xl font-bold tracking-tight">Edit Page</h2>
                    <p className="text-muted-foreground">Update content, metadata, and status.</p>
                </div>

                <form onSubmit={submit} className="grid gap-4 xl:grid-cols-12">
                    <div className="space-y-4 xl:col-span-9">
                        <Card>
                            <CardContent className="space-y-5 pt-6">
                                <div className="grid gap-2">
                                    <Label htmlFor="title">Title</Label>
                                    <Input
                                        id="title"
                                        value={data.title}
                                        onChange={(e) => setData('title', e.target.value)}
                                        required
                                    />
                                    {errors.title && <p className="text-sm text-destructive">{errors.title as string}</p>}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="slug">Permalink</Label>
                                    <Input
                                        id="slug"
                                        value={data.slug}
                                        onChange={(e) => setData('slug', e.target.value)}
                                        placeholder="my-awesome-page"
                                    />
                                    {permalinkPreview && (
                                        <p className="text-sm text-muted-foreground">
                                            Preview:{' '}
                                            <a href={permalinkPreview} className="text-primary hover:underline" target="_blank" rel="noreferrer">
                                                {permalinkPreview}
                                            </a>
                                        </p>
                                    )}
                                    {errors.slug && <p className="text-sm text-destructive">{errors.slug as string}</p>}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="meta_description">Description</Label>
                                    <Textarea
                                        id="meta_description"
                                        value={data.meta_description}
                                        onChange={(e) => setData('meta_description', e.target.value)}
                                        rows={3}
                                    />
                                    {errors.meta_description && (
                                        <p className="text-sm text-destructive">{errors.meta_description as string}</p>
                                    )}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="content">Content</Label>
                                    <RichTextEditor
                                        value={data.content}
                                        onChange={(value) => setData('content', value)}
                                        placeholder="Write your page content..."
                                    />
                                    {errors.content && <p className="text-sm text-destructive">{errors.content as string}</p>}
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>SEO & Media</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="image">Image URL</Label>
                                    <Input
                                        id="image"
                                        value={data.image}
                                        onChange={(e) => setData('image', e.target.value)}
                                        placeholder="https://..."
                                    />
                                    {errors.image && <p className="text-sm text-destructive">{errors.image as string}</p>}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="tags">Tags (comma separated)</Label>
                                    <Input id="tags" value={data.tags} onChange={(e) => setData('tags', e.target.value)} />
                                    {errors.tags && <p className="text-sm text-destructive">{errors.tags as string}</p>}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="meta_title">Meta Title</Label>
                                    <Input
                                        id="meta_title"
                                        value={data.meta_title}
                                        onChange={(e) => setData('meta_title', e.target.value)}
                                    />
                                    {errors.meta_title && <p className="text-sm text-destructive">{errors.meta_title as string}</p>}
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="meta_keywords">Meta Keywords</Label>
                                    <Input
                                        id="meta_keywords"
                                        value={data.meta_keywords}
                                        onChange={(e) => setData('meta_keywords', e.target.value)}
                                    />
                                    {errors.meta_keywords && <p className="text-sm text-destructive">{errors.meta_keywords as string}</p>}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-4 xl:col-span-3">
                        <Card>
                            <CardHeader>
                                <CardTitle>Publish</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <Button type="submit" className="w-full" disabled={processing}>
                                    Save
                                </Button>
                                <Button type="button" variant="outline" className="w-full" onClick={submitAndExit} disabled={processing}>
                                    Save & Exit
                                </Button>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Status</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2">
                                <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {STATUS_OPTIONS.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.status && <p className="text-sm text-destructive">{errors.status as string}</p>}
                            </CardContent>
                        </Card>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
