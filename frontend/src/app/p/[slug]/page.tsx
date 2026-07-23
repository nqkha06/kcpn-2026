import { PublicLayout } from "@/components/layouts/public-layout";
import { getPublicPage } from "@/lib/api/public-server";
import type { Metadata } from "next";
import { notFound } from "next/navigation";

interface PublicPageProps {
  params: Promise<{ slug: string }>;
}

export async function generateMetadata({ params }: PublicPageProps): Promise<Metadata> {
  const { slug } = await params;
  const page = await getPublicPage(slug);

  if (!page) {
    return {};
  }

  return {
    title: page.meta_title || page.title,
    description: page.meta_description || undefined,
    keywords: page.meta_keywords || undefined,
  };
}

export default async function PublicPageShow({ params }: PublicPageProps) {
  const { slug } = await params;
  const page = await getPublicPage(slug);

  if (!page) {
    notFound();
  }

  return (
    <PublicLayout>
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
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={page.image} alt={page.title} className="h-auto w-full object-cover" />
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
    </PublicLayout>
  );
}
