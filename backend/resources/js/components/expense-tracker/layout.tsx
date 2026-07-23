import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowLeftRight,
    ChevronUp,
    Circle,
    Gauge,
    PiggyBank,
    Settings2,
    Wallet,
} from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import type { ComponentType, PropsWithChildren } from 'react';
import { toast } from 'sonner';
import TrackerFooter from '@/components/expense-tracker/footer';
import TrackerHeader from '@/components/expense-tracker/header';
import { Toaster } from '@/components/ui/sonner';
import type {
    TrackerNavigationItem,
    TrackerProfile,
} from '@/types/expense-tracker';

interface TrackerLayoutProps extends PropsWithChildren {
    title: string;
    heading: string;
    description?: string;
    action?: React.ReactNode;
    activePath: string;
    navigation: TrackerNavigationItem[];
    profile?: TrackerProfile;
    showSidebar?: boolean;
}

type NavigationIcon = ComponentType<{ className?: string }>;

function normalizePath(pathOrUrl: string): string {
    const fallbackPath = pathOrUrl.split('?')[0]?.split('#')[0] ?? '/';

    try {
        const parsedUrl = new URL(pathOrUrl, window.location.origin);
        const normalizedPath = parsedUrl.pathname.replace(/\/$/, '');

        return normalizedPath === '' ? '/' : normalizedPath;
    } catch {
        const normalizedPath = fallbackPath.replace(/\/$/, '');

        return normalizedPath === '' ? '/' : normalizedPath;
    }
}

function isPathActive(activePath: string, itemHref: string): boolean {
    const normalizedActivePath = normalizePath(activePath);
    const normalizedItemPath = normalizePath(itemHref);

    if (normalizedActivePath === normalizedItemPath) {
        return true;
    }

    if (normalizedItemPath === '/') {
        return normalizedActivePath === '/';
    }

    return normalizedActivePath.startsWith(`${normalizedItemPath}/`);
}

const navigationIconMap: Record<string, NavigationIcon> = {
    dashboard: Gauge,
    transactions: ArrowLeftRight,
    budgets: PiggyBank,
    wallets: Wallet,
    settings: Settings2,
    'tổng quan': Gauge,
    'giao dịch': ArrowLeftRight,
    'ngân sách': PiggyBank,
    'ví tiền': Wallet,
    'cài đặt': Settings2,
};

function resolveNavigationIcon(item: TrackerNavigationItem): NavigationIcon {
    const normalizedLabel = item.label.trim().toLowerCase();

    if (navigationIconMap[normalizedLabel]) {
        return navigationIconMap[normalizedLabel];
    }

    if (item.href.includes('dashboard')) {
        return Gauge;
    }

    if (item.href.includes('transactions')) {
        return ArrowLeftRight;
    }

    if (item.href.includes('budgets')) {
        return PiggyBank;
    }

    if (item.href.includes('wallet')) {
        return Wallet;
    }

    if (item.href.includes('setting')) {
        return Settings2;
    }

    return Circle;
}

interface MobileTrackerNavigationProps {
    activePath: string;
    navigation: TrackerNavigationItem[];
}

function MobileTrackerNavigation({
    activePath,
    navigation,
}: MobileTrackerNavigationProps) {
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const quickNavigation = navigation.slice(0, 3);
    const overflowNavigation = navigation.slice(3);
    const isOverflowActive = overflowNavigation.some(
        (item) => isPathActive(activePath, item.href),
    );

    return (
        <div className="fixed inset-x-0 bottom-0 z-50 md:hidden">
            <div className="mx-auto w-full max-w-7xl px-4 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                <div className="relative">
                    <div
                        className={[
                            'absolute inset-x-0 bottom-[calc(100%+0.75rem)] origin-bottom rounded-3xl border border-slate-200/80 bg-white/95 p-2 shadow-xl shadow-slate-900/10 backdrop-blur transition-all duration-300',
                            isMenuOpen
                                ? 'translate-y-0 opacity-100'
                                : 'pointer-events-none translate-y-4 opacity-0',
                        ].join(' ')}
                    >
                        <p className="px-2 pb-2 text-[11px] font-semibold tracking-[0.14em] text-slate-400 uppercase">
                            Điều hướng
                        </p>
                        <ul className="space-y-1">
                            {navigation.map((item) => {
                                const isActive = isPathActive(
                                    activePath,
                                    item.href,
                                );
                                const Icon = resolveNavigationIcon(item);

                                return (
                                    <li key={item.href}>
                                        <Link
                                            href={item.href}
                                            aria-current={
                                                isActive ? 'page' : undefined
                                            }
                                            onClick={() => {
                                                setIsMenuOpen(false);
                                            }}
                                            className={[
                                                'flex items-center justify-between rounded-2xl px-3 py-3 text-sm font-medium transition-colors',
                                                isActive
                                                    ? 'bg-blue-50 text-blue-700'
                                                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900',
                                            ].join(' ')}
                                        >
                                            <span className="flex items-center gap-3">
                                                <span
                                                    className={[
                                                        'inline-flex size-9 items-center justify-center rounded-xl',
                                                        isActive
                                                            ? 'bg-blue-100 text-blue-700'
                                                            : 'bg-slate-100 text-slate-500',
                                                    ].join(' ')}
                                                >
                                                    <Icon className="size-4" />
                                                </span>
                                                <span>{item.label}</span>
                                            </span>
                                            {isActive ? (
                                                <span className="rounded-full bg-blue-100 px-2 py-1 text-[11px] font-semibold text-blue-700">
                                                    Hiện tại
                                                </span>
                                            ) : null}
                                        </Link>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>

                    <nav
                        aria-label="Điều hướng di động"
                        className="rounded-3xl border border-slate-200/80 bg-white/95 p-2 shadow-lg shadow-slate-900/10 backdrop-blur"
                    >
                        <ul className="grid grid-cols-4 gap-1">
                            {quickNavigation.map((item) => {
                                const isActive = isPathActive(
                                    activePath,
                                    item.href,
                                );
                                const Icon = resolveNavigationIcon(item);

                                return (
                                    <li key={item.href}>
                                        <Link
                                            href={item.href}
                                            aria-current={
                                                isActive ? 'page' : undefined
                                            }
                                            onClick={() => {
                                                setIsMenuOpen(false);
                                            }}
                                            className={[
                                                'relative flex min-h-14 flex-col items-center justify-center rounded-2xl px-2 py-2 text-center text-[11px] font-semibold transition-all',
                                                isActive
                                                    ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
                                                    : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 active:scale-[0.98] active:bg-slate-200 active:text-slate-900',
                                            ].join(' ')}
                                        >
                                            <Icon
                                                className={[
                                                    'size-5 transition-transform duration-300',
                                                    isActive
                                                        ? 'scale-105'
                                                        : '',
                                                ].join(' ')}
                                            />
                                            <span className="mt-1 truncate">
                                                {item.label}
                                            </span>
                                            {isActive ? (
                                                <span className="mt-1 h-1 w-6 rounded-full bg-blue-600/80" />
                                            ) : null}
                                        </Link>
                                    </li>
                                );
                            })}

                            <li>
                                <button
                                    type="button"
                                    aria-expanded={isMenuOpen}
                                    aria-label={
                                        isMenuOpen
                                            ? 'Đóng menu điều hướng'
                                            : 'Mở menu điều hướng'
                                    }
                                    onClick={() => {
                                        setIsMenuOpen(
                                            (currentState) => !currentState,
                                        );
                                    }}
                                    className={[
                                        'flex min-h-14 w-full flex-col items-center justify-center rounded-2xl px-2 py-2 text-center text-[11px] font-semibold transition-all',
                                        isMenuOpen || isOverflowActive
                                            ? 'bg-blue-50 text-blue-700'
                                            : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900 active:scale-[0.98] active:bg-slate-200 active:text-slate-900',
                                    ].join(' ')}
                                >
                                    <span
                                        className={[
                                            'inline-flex size-8 items-center justify-center rounded-full bg-slate-100 transition-transform duration-300',
                                            isMenuOpen
                                                ? 'rotate-180 bg-blue-100 text-blue-700'
                                                : '',
                                        ].join(' ')}
                                    >
                                        <ChevronUp className="size-4" />
                                    </span>
                                    <span className="mt-1">
                                        {isMenuOpen ? 'Đóng' : 'Menu'}
                                    </span>
                                </button>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    );
}

export default function TrackerLayout({
    title,
    heading,
    description,
    action,
    activePath,
    navigation,
    profile,
    children,
}: TrackerLayoutProps) {
    const page = usePage<{ flash?: Record<string, string | null> }>();
    const previousFlash = useRef<Record<string, string | null> | null>(null);

    useEffect(() => {
        const flash = page.props.flash ?? null;

        if (JSON.stringify(flash) === JSON.stringify(previousFlash.current)) {
            return;
        }

        previousFlash.current = flash;

        if (flash?.success) {
            toast.success(flash.success, { position: 'top-right' });
        }

        if (flash?.error) {
            toast.error(flash.error, { position: 'top-right' });
        }

        if (flash?.info) {
            toast.info(flash.info, { position: 'top-right' });
        }

        if (flash?.warning) {
            toast.warning(flash.warning, { position: 'top-right' });
        }
    }, [page.props.flash]);

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <Head title={title} />
            <TrackerHeader
                navigation={navigation}
                activePath={activePath}
                profile={profile}
            />

            <main className="mx-auto w-full max-w-7xl px-4 py-8 pb-28 sm:px-6 md:pb-8">
                <div className="mb-6 flex flex-col items-start justify-between gap-4 md:flex-row md:items-center">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">
                            {heading}
                        </h1>
                        {description ? (
                            <p className="mt-2 text-sm">{description}</p>
                        ) : null}
                    </div>
                    {action ? <div className="mt-4">{action}</div> : null}
                </div>

                <div className="flex items-start gap-6">
                    <section className="w-full">{children}</section>
                </div>
            </main>

            <TrackerFooter />

            <MobileTrackerNavigation
                navigation={navigation}
                activePath={activePath}
            />

            <Toaster />
        </div>
    );
}
