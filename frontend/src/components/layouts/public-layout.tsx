"use client";

import { useAuth } from "@/lib/auth/auth-context";
import { publicConfigurationQueryKey, publicSiteService } from "@/services/public-site.service";
import type { SiteMenuItem } from "@/types/site";
import { useQuery } from "@tanstack/react-query";
import Link from "next/link";
import { Lock, ChevronUp } from 'lucide-react';
import { useEffect, type ReactNode, type SVGProps } from "react";

interface PublicMenuChild {
    id: number;
    title: string;
    url: string | null;
    target: '_self' | '_blank';
}

interface PublicMenuItem extends PublicMenuChild {
    children: PublicMenuChild[];
}

const defaultHeaderMenus: PublicMenuItem[] = [
    { id: 1, title: 'Tính năng', url: '/#features', target: '_self', children: [] },
    { id: 2, title: 'Cách hoạt động', url: '/#how-it-works', target: '_self', children: [] },
    { id: 3, title: 'Câu hỏi thường gặp', url: '/#templates', target: '_self', children: [] },
    { id: 4, title: 'Liên hệ', url: '/#contact', target: '_self', children: [] },
];

const defaultFooterMenus: PublicMenuItem[] = [
    {
        id: 11,
        title: 'Sản phẩm',
        url: null,
        target: '_self',
        children: [
            { id: 111, title: 'Cách hoạt động', url: '/#how-it-works', target: '_self' },
            { id: 112, title: 'Bảng xếp hạng', url: '/leaderboard', target: '_self' },
        ],
    },
    {
        id: 12,
        title: 'Công ty',
        url: null,
        target: '_self',
        children: [
            { id: 121, title: 'Về chúng tôi', url: '/about', target: '_self' },
            { id: 122, title: 'Liên hệ', url: '/#contact', target: '_self' },
        ],
    },
    {
        id: 13,
        title: 'Hỗ trợ',
        url: null,
        target: '_self',
        children: [
            { id: 131, title: 'Trung tâm trợ giúp', url: '#', target: '_self' },
            { id: 132, title: 'Chính sách bảo mật', url: '#', target: '_self' },
        ],
    },
];

function normalizeMenus(items: SiteMenuItem[]): PublicMenuItem[] {
    return items.map((item) => ({
        id: item.id,
        title: item.title,
        url: item.url,
        target: item.target === '_blank' ? '_blank' : '_self',
        children: item.children.map((child) => ({
            id: child.id,
            title: child.title,
            url: child.url,
            target: child.target === '_blank' ? '_blank' : '_self',
        })),
    }));
}

function SocialIcon({ name, ...props }: SVGProps<SVGSVGElement> & { name: "facebook" | "twitter" | "instagram" }) {
    return (
        <svg
            {...props}
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
        >
            {name === "facebook" ? (
                <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
            ) : null}
            {name === "twitter" ? (
                <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z" />
            ) : null}
            {name === "instagram" ? (
                <>
                    <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
                    <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                    <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
                </>
            ) : null}
        </svg>
    );
}

function isExternalUrl(url: string): boolean {
    return /^https?:\/\//.test(url);
}

function MenuLink({
    item,
    className,
}: {
    item: { title: string; url: string | null; target: '_self' | '_blank' };
    className: string;
}) {
    const href = item.url?.trim() || '#';

    if (isExternalUrl(href)) {
        return (
            <a
                href={href}
                target={item.target}
                rel={item.target === '_blank' ? 'noopener noreferrer' : undefined}
                className={className}
            >
                {item.title}
            </a>
        );
    }

    if (href.startsWith('/')) {
        return (
            <Link href={href} className={className}>
                {item.title}
            </Link>
        );
    }

    return (
        <a href={href} className={className}>
            {item.title}
        </a>
    );
}

interface LayoutProps {
    children: ReactNode;
}

export function PublicLayout({ children }: LayoutProps) {
    const { isAuthenticated } = useAuth();
    const configurationQuery = useQuery({
        queryKey: publicConfigurationQueryKey,
        queryFn: publicSiteService.configuration,
        staleTime: 30 * 60 * 1000,
    });
    const configuration = configurationQuery.data;
    const headerMenus = configuration?.menus.home_header.length
        ? normalizeMenus(configuration.menus.home_header)
        : defaultHeaderMenus;
    const footerMenus = configuration?.menus.home_footer.length
        ? normalizeMenus(configuration.menus.home_footer)
        : defaultFooterMenus;
    const appearanceOptions = configuration?.appearance;
    const brandName = appearanceOptions?.site_name?.trim() || 'Spendify';
    const backendUrl = process.env.NEXT_PUBLIC_BACKEND_URL?.replace(/\/$/, '') ?? '';
    const brandLogo =
        appearanceOptions?.logo_light ||
        appearanceOptions?.logo_dark ||
        `${backendUrl}/logo.png`;
    const tagline =
        appearanceOptions?.tagline?.trim() ||
        'Cách quản lý chi tiêu thông minh và nhẹ nhàng hơn mỗi ngày. Theo dõi thu chi rõ ràng, duy trì thói quen tài chính bền vững.';

    useEffect(() => {
        const backToTopBtn = document.getElementById('backToTop');
        const handleScroll = () => {
            if (window.scrollY > 400) {
                backToTopBtn?.classList.remove('opacity-0', 'pointer-events-none');
                backToTopBtn?.classList.add('opacity-100', 'pointer-events-auto');
            } else {
                backToTopBtn?.classList.add('opacity-0', 'pointer-events-none');
                backToTopBtn?.classList.remove('opacity-100', 'pointer-events-auto');
            }
        };

        window.addEventListener('scroll', handleScroll);

        return () => window.removeEventListener('scroll', handleScroll);
    }, []);

    const scrollToTop = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };

    return (
        <div className="bg-gray-50 text-slate-800 antialiased selection:bg-brand-500 selection:text-white min-h-screen flex flex-col font-sans">
            {/* Navigation */}
            <nav className="fixed top-0 w-full z-50 transition-all duration-300 bg-white/80 backdrop-blur-md border-b border-gray-100">
                <div className="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
                    <Link href="/" className="flex items-center gap-2">
                        <div className="w-10 h-10">
                            {/* eslint-disable-next-line @next/next/no-img-element */}
                            <img className='w-full h-full object-cover rounded-xl shadow-sm' src={brandLogo} alt={`${brandName} Logo`} />
                        </div>
                        <span className="text-xl font-bold tracking-tight text-slate-900">{brandName}</span>
                    </Link>

                    <div className="hidden md:flex items-center gap-8 font-medium text-slate-600">
                        {headerMenus.map((menu) => (
                            <div key={menu.id} className="group relative">
                                <MenuLink item={menu} className="hover:text-brand-600 transition-colors" />

                                {menu.children.length > 0 && (
                                    <div className="invisible absolute left-0 top-full z-50 mt-2 w-52 rounded-xl border border-gray-200 bg-white p-2 opacity-0 shadow-lg transition-all duration-150 group-hover:visible group-hover:opacity-100">
                                        {menu.children.map((child) => (
                                            <MenuLink
                                                key={child.id}
                                                item={child}
                                                className="block rounded-md px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 hover:text-brand-600"
                                            />
                                        ))}
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>

                    <div className="flex items-center gap-4">
                        {isAuthenticated ? (
                            <Link href="/dashboard" className="bg-slate-900 hover:bg-slate-800 text-white px-6 py-2.5 rounded-full font-medium transition-colors shadow-sm">
                                Tổng quan
                            </Link>
                        ) : (
                            <>
                                <Link href="/login" className="hidden sm:block font-medium text-slate-600 hover:text-brand-600 transition-colors">Đăng nhập</Link>
                                <Link href="/register" className="bg-slate-900 hover:bg-slate-800 text-white px-6 py-2.5 rounded-full font-medium transition-colors shadow-sm">
                                    Đăng ký miễn phí
                                </Link>
                            </>
                        )}
                    </div>
                </div>
            </nav>

            {/* Main Content */}
            <main className="flex-grow pt-20">
                {children}
            </main>

            {/* Footer */}

            <footer className="bg-white pt-20 pb-10 border-t border-gray-200 mt-auto">
                <div className="max-w-7xl mx-auto px-6">
                    <div className="grid grid-cols-2 lg:grid-cols-5 gap-10 mb-16">
                        {/* Branding */}
                        <div className="col-span-2">
                            <div className="flex items-center gap-2 mb-6">
                                <div className="w-10 h-10">
                                    {/* eslint-disable-next-line @next/next/no-img-element */}
                            <img className='w-full h-full object-cover rounded-xl shadow-sm' src={brandLogo} alt={`${brandName} Logo`} />
                                </div>
                                <span className="text-xl font-bold tracking-tight text-slate-900">{brandName}</span>
                            </div>
                            <p className="text-slate-500 text-sm leading-relaxed mb-6 max-w-sm">
                                {tagline}
                            </p>
                            <div className="flex items-center gap-4">
                                <a href="#" className="w-10 h-10 bg-white border border-gray-200 rounded-full flex items-center justify-center text-slate-600 hover:text-brand-500 hover:border-brand-500 transition-colors">
                                    <SocialIcon name="facebook" className="w-5 h-5" />
                                </a>
                                <a href="#" className="w-10 h-10 bg-white border border-gray-200 rounded-full flex items-center justify-center text-slate-600 hover:text-brand-500 hover:border-brand-500 transition-colors">
                                    <SocialIcon name="twitter" className="w-5 h-5" />
                                </a>
                                <a href="#" className="w-10 h-10 bg-white border border-gray-200 rounded-full flex items-center justify-center text-slate-600 hover:text-brand-500 hover:border-brand-500 transition-colors">
                                    <SocialIcon name="instagram" className="w-5 h-5" />
                                </a>
                            </div>
                        </div>

                        {footerMenus.map((menu) => (
                            <div key={menu.id}>
                                <h4 className="font-bold text-slate-900 mb-6">{menu.title}</h4>
                                <ul className="space-y-4 text-sm text-slate-500">
                                    {menu.children.length > 0 ? (
                                        menu.children.map((child) => (
                                            <li key={child.id}>
                                                <MenuLink item={child} className="hover:text-brand-600 transition-colors" />
                                            </li>
                                        ))
                                    ) : (
                                        <li>
                                            <MenuLink item={menu} className="hover:text-brand-600 transition-colors" />
                                        </li>
                                    )}
                                </ul>
                            </div>
                        ))}
                    </div>

                    <div className="border-t border-gray-200 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                        <p className="text-sm text-slate-500">
                            &copy; {new Date().getFullYear()} {brandName}. Đã đăng ký bản quyền.
                        </p>
                        <div className="flex items-center gap-4">
                            <div className="flex items-center gap-2 text-xs font-bold text-slate-400 bg-white px-3 py-1.5 rounded-lg border border-gray-200">
                                <Lock className="w-4 h-4" />
                                MÃ HÓA 256-BIT
                            </div>
                        </div>
                    </div>
                </div>
            </footer>


            {/* Back to Top */}
            <button
                id="backToTop"
                onClick={scrollToTop}
                className="fixed bottom-8 right-8 z-50 bg-slate-900 text-white w-14 h-14 rounded-full shadow-lg flex items-center justify-center hover:bg-brand-500 hover:-translate-y-1 transition-all duration-300 opacity-0 pointer-events-none"
            >
                <ChevronUp className="w-6 h-6" />
            </button>
        </div>
    );
}
