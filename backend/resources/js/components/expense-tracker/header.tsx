import { Link, usePage } from '@inertiajs/react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { logout } from '@/routes';
import type {
    TrackerNavigationItem,
    TrackerProfile,
} from '@/types/expense-tracker';

interface TrackerHeaderProps {
    navigation: TrackerNavigationItem[];
    activePath: string;
    profile?: TrackerProfile;

}

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

export default function TrackerHeader({
    navigation,
    activePath,
    profile,
}: TrackerHeaderProps) {
    const page = usePage<any>();
    const homeLink = navigation[0]?.href ?? '/user';
    const appearanceOptions = page.props?.appearanceOptions ?? {};
    const brandName = appearanceOptions.site_name || 'Spendify';
    const brandLogo = appearanceOptions.logo_light || appearanceOptions.logo_dark || '/logo.png';

    return (
        <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div className="mx-auto flex h-18 w-full max-w-7xl items-center justify-between px-4 sm:px-6">
                <Link href={homeLink} className="flex items-center gap-2">
                    <div className="w-9 h-9">
                        <img className='w-full h-full object-cover rounded-xl shadow-sm' src={brandLogo} alt={`${brandName} Logo`} />
                    </div>
                    <span className="text-lg font-semibold tracking-tight text-slate-900">
                        {brandName}
                    </span>
                </Link>

               <nav className="hidden self-stretch items-stretch gap-1 md:flex">
                    {navigation.map((item) => {
                        const isActive = isPathActive(activePath, item.href);

                        return (
                            <Link
                                key={item.href}
                                href={item.href}
                                className={[
                                    'relative inline-flex h-full items-center px-3 text-sm font-medium transition-colors',
                                    'after:absolute after:bottom-0 after:left-3 after:right-3 after:h-[2px] after:rounded-full after:transition-all',
                                    isActive
                                        ? 'text-blue-700 after:bg-blue-600'
                                        : 'text-slate-600 hover:text-slate-900 after:bg-transparent hover:after:bg-slate-300',
                                ].join(' ')}
                            >
                                {item.label}
                            </Link>
                        );
                    })}
                </nav>

                {profile ? (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <button
                                type="button"
                                className="flex items-center gap-3 rounded-xl px-2 py-1.5 transition-colors hover:bg-slate-100"
                            >
                                <div className="hidden text-right sm:block">
                                    <p className="text-sm font-semibold text-slate-900">
                                        {profile.name}
                                    </p>
                                    <p className="text-xs text-slate-500">
                                        {profile.email}
                                    </p>
                                </div>
                                <div className="flex size-9 items-center justify-center rounded-full bg-blue-100 font-semibold text-blue-700">
                                    {profile.initials}
                                </div>
                            </button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" className="w-56">
                            <DropdownMenuLabel>
                                <div className="flex flex-col">
                                    <span className="text-sm font-semibold text-slate-900">
                                        {profile.name}
                                    </span>
                                    <span className="text-xs text-slate-500">
                                        {profile.email}
                                    </span>
                                </div>
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem asChild>
                                <Link
                                        href="/user/settings"
                                    className="cursor-pointer"
                                >
                                    Cài đặt
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem asChild>
                                <Link
                                    href={logout()}
                                    method="post"
                                    as="button"
                                    className="w-full cursor-pointer"
                                >
                                    Đăng xuất
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                ) : null}
            </div>
        </header>
    );
}
