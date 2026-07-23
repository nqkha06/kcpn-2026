import Layout from '@/components/Layout';

interface PublicPage {
    id: number;
    title: string;
    slug: string;
    image?: string | null;
    content?: string | null;
    meta_title?: string | null;
    meta_description?: string | null;
    meta_keywords?: string | null;
}

interface PageShowProps {
    page: PublicPage;
}

export default function Show({ page }: PageShowProps) {
    return (
        <Layout title={page.meta_title || page.title}>
            <section className="mx-auto w-full max-w-5xl px-6 py-12 md:py-16">
                <header className="mb-8 pb-6">
                    <h1 className="text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">
                        {page.title}
                    </h1>
                    {page.meta_description ? (
                        <p className="mt-3 text-base leading-relaxed text-slate-600">
                            {page.meta_description}
                        </p>
                    ) : null}
                </header>

                {page.image ? (
                    <div className="mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        <img
                            src={page.image}
                            alt={page.title}
                            className="h-auto w-full object-cover"
                        />
                    </div>
                ) : null}

                {page.content ? (
                    <article
                        className="space-y-4 leading-7 text-slate-700"
                        dangerouslySetInnerHTML={{ __html: page.content }}
                    />
                ) : (
                    <p className="text-slate-500">Nội dung đang được cập nhật.</p>
                )}
            </section>
        </Layout>
    );
}
