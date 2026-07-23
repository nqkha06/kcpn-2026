import { Head, Link, usePage } from '@inertiajs/react';
import {
    Wallet, LayoutDashboard, Search, ArrowDownToLine, Receipt,
    Sparkles, Trophy, Users, User, CreditCard, Info,
    Settings, LifeBuoy, Coins, Bell, ChevronDown, LogOut
} from 'lucide-react';
import type { ReactNode} from 'react';
import React, { useMemo } from 'react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu, DropdownMenuContent, DropdownMenuItem,
    DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Toaster } from '@/components/ui/sonner';
import { useInitials } from '@/hooks/use-initials';
import { cn } from '@/lib/utils'; // Giả sử bạn có hàm cn từ shadcn/ui
import { logout } from '@/routes';
import users from "@/routes/admin/users";
import { edit } from '@/routes/profile';
import { index as memberCashbackIndex } from '@/actions/App/Http/Controllers/Member/CashbackController';

// --- Interfaces ---
interface NavItem {
    label: string;
    href: string;
    icon: React.ElementType;
    isExternal?: boolean;
}

interface DashboardLayoutProps {
    children: ReactNode;
    title?: string;
}

// --- Constants ---
const MENU_GROUPS = [
    {
        group: "Menu",
        items: [
            { label: 'Dashboard', href: '/member', icon: LayoutDashboard },
            { label: 'Cashback Home', href: memberCashbackIndex().url, icon: Search },
            { label: 'Withdraw', href: '/member/withdraw', icon: ArrowDownToLine },
            { label: 'Transactions', href: '/member/transactions', icon: Receipt, isExternal: true },
            { label: 'Top Products', href: '/member/products', icon: Sparkles },
        ]
    },
    {
        group: "Community",
        items: [
            { label: 'Leaderboard', href: '/member/leaderboard', icon: Trophy },
            { label: 'Refer & Earn', href: '/member/referral', icon: Users },
        ]
    },
    {
        group: "Account",
        items: [
            { label: 'Profile', href: '/member/profile', icon: User },
            { label: 'Payment Methods', href: '/member/payment', icon: CreditCard },
            { label: 'About Platform', href: '/member/about', icon: Info },
        ]
    }
];

// --- Sub-components ---
const NavLink = ({ item, active }: { item: NavItem, active: boolean }) => {
    const Component = item.isExternal ? 'a' : Link;
    const baseClass = "flex items-center gap-3 px-3 py-2.5 rounded-xl transition-colors font-medium";

    return (
        <Component
            href={item.href}
            className={cn(
                baseClass,
                active
                    ? "bg-brand-50 text-brand-600 font-bold"
                    : "text-slate-500 hover:bg-gray-50 hover:text-slate-900"
            )}
        >
            <item.icon className="w-5 h-5" />
            <span>{item.label}</span>
        </Component>
    );
};

export default function DashboardLayout({ children, title = 'Dashboard - Backcash' }: DashboardLayoutProps) {
    const { url, props } = usePage();
    const auth = props.auth as any; // Nên thay any bằng Interface User chuẩn
    const getInitials = useInitials();

    const checkActive = (path: string) => {
        if (path === '/member') return url === '/member';
        return url.startsWith(path);
    };

    return (
        <div className="text-slate-800 antialiased selection:bg-brand-500 selection:text-white flex h-screen overflow-hidden bg-gray-50 font-sans">
            <Head title={title}>
                <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
            </Head>

            {/* Desktop Sidebar */}
            <aside className="hidden md:flex flex-col w-64 bg-white border-r border-gray-100 h-full flex-shrink-0 relative z-20">
                <div className="h-20 flex items-center px-6 border-b border-gray-50">
                    <Link href="/member" className="flex items-center gap-2">
                        <div className="w-10 h-10 bg-gradient-to-br from-brand-400 to-orange-500 rounded-xl flex items-center justify-center text-white shadow-sm">
                            <Wallet className="w-6 h-6" />
                        </div>
                        <span className="text-xl font-bold tracking-tight text-slate-900">Backcash</span>
                    </Link>
                </div>

                <nav className="flex-1 overflow-y-auto py-6 px-4 space-y-8 [scrollbar-width:none]">
                    {MENU_GROUPS.map((group) => (
                        <div key={group.group}>
                            <div className="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 px-3">
                                {group.group}
                            </div>
                            <div className="space-y-1">
                                {group.items.map((item) => (
                                    <NavLink
                                        key={item.href}
                                        item={item}
                                        active={checkActive(item.href)}
                                    />
                                ))}
                            </div>
                        </div>
                    ))}
                </nav>

                <div className="p-4 border-t border-gray-50 space-y-1">
                    <NavLink item={{ label: 'Settings', href: '#', icon: Settings, isExternal: true }} active={false} />
                    <NavLink item={{ label: 'Help Center', href: '#', icon: LifeBuoy, isExternal: true }} active={false} />
                </div>
            </aside>

            {/* Main Content Area */}
            <main className="flex-1 flex flex-col h-full bg-slate-50 relative overflow-hidden">
                <header className="h-20 bg-white/80 backdrop-blur-md border-b border-gray-100 flex items-center justify-between px-6 z-10 flex-shrink-0">
                    <div className="md:hidden w-8 h-8 bg-gradient-to-br from-brand-400 to-orange-500 rounded-lg flex items-center justify-center text-white">
                        <Wallet className="w-4 h-4" />
                    </div>

                    <div className="hidden md:flex max-w-md w-full relative">
                        <Search className="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input
                            type="text"
                            placeholder="Search stores, products..."
                            className="w-full bg-gray-50 border border-gray-200 text-sm rounded-full pl-10 p-2.5 outline-none focus:ring-2 focus:ring-brand-500/20 transition-all"
                        />
                    </div>

                    <div className="flex items-center gap-3">
                        <div className="hidden sm:flex items-center gap-2 bg-green-50 px-3 py-1.5 rounded-full border border-green-100">
                            <Coins className="w-4 h-4 text-green-600" />
                            <span className="font-bold text-green-700 text-sm">$340.50</span>
                        </div>

                        <NotificationDropdown />

                        <div className="h-8 w-px bg-gray-200 mx-2 hidden md:block" />

                        <UserDropdown auth={auth} getInitials={getInitials} />
                    </div>
                </header>

                <div className="flex-1 overflow-y-auto p-4 md:p-8 pb-24 md:pb-8 [scrollbar-width:none]">
                    <div className="max-w-6xl mx-auto space-y-6 min-h-[calc(100vh-200px)]">
                        {children}
                    </div>

                    <footer className="max-w-6xl mx-auto border-t border-gray-100 pt-6 mt-12 flex flex-col md:flex-row items-center justify-between text-sm text-slate-500 gap-4">
                        <div className="flex items-center gap-2">
                            <span className="font-bold text-slate-700">HoanTienNhanh</span>
                            <span>© {new Date().getFullYear()}</span>
                        </div>
                        <div className="flex gap-6">
                            {['Terms', 'Privacy', 'Help'].map(link => (
                                <a key={link} href="#" className="hover:text-brand-500 transition-colors">{link}</a>
                            ))}
                        </div>
                    </footer>
                </div>
            </main>

            {/* Mobile Bottom Nav */}
            <MobileNavigation url={url} checkActive={checkActive} />

            <Toaster position="top-right" />
        </div>
    );
}

// --- Tách các Dropdown nhỏ để code chính gọn hơn ---
function NotificationDropdown() {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger className="relative w-10 h-10 rounded-full flex items-center justify-center text-slate-500 hover:bg-gray-100 transition-colors">
                <Bell className="w-5 h-5" />
                <span className="absolute top-2 right-2.5 w-2 h-2 bg-brand-500 rounded-full border-2 border-white" />
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-80 p-4 text-center">
                <DropdownMenuLabel className="font-bold text-left">Notifications</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <div className="py-8">
                    <Bell className="w-8 h-8 mx-auto text-slate-300 mb-2" />
                    <p className="text-sm text-slate-500">No new notifications right now.</p>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

function UserDropdown({ auth, getInitials }: any) {
    const user = auth?.user;
    return (
        <DropdownMenu>
            <DropdownMenuTrigger className="flex items-center gap-3 focus:outline-none">
                <Avatar className="w-9 h-9 border-2 border-white shadow-sm">
                    <AvatarImage src={user?.avatar} />
                    <AvatarFallback className="bg-brand-100 text-brand-600 font-bold text-xs">
                        {user ? getInitials(user.name) : '??'}
                    </AvatarFallback>
                </Avatar>
                <div className="hidden md:block text-left">
                    <p className="text-sm font-bold text-slate-900 leading-tight">{user?.name || 'Guest'}</p>
                    <p className="text-[11px] text-slate-500 font-medium uppercase">Free Member</p>
                </div>
                <ChevronDown className="hidden md:block w-4 h-4 text-slate-400" />
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-56">
                <DropdownMenuLabel>
                    <p className="text-sm font-medium">{user?.name}</p>
                    <p className="text-xs text-slate-500">{user?.email}</p>
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <Link href={edit()} className="w-full cursor-pointer"><Settings className="mr-2 h-4 w-4" /> Settings</Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <Link href={logout()} method="post" as="button" className="w-full text-red-600"><LogOut className="mr-2 h-4 w-4" /> Log out</Link>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}

function MobileNavigation({ url, checkActive }: { url: string, checkActive: (p: string) => boolean }) {
    const mobileItems = [
        { href: '/member', icon: LayoutDashboard },
        { href: '/member/cashback', icon: Search },
        { href: '/member/leaderboard', icon: Trophy },
        { href: '/member/referral', icon: Users },
        { href: '/member/profile', icon: User },
    ];

    return (
        <div className="md:hidden fixed bottom-6 left-4 right-4 z-50">
            <div className="bg-white/95 backdrop-blur-xl border border-gray-100 shadow-xl rounded-3xl p-2 flex justify-around items-center">
                {mobileItems.map((item) => {
                    const active = checkActive(item.href);
                    return (
                        <Link
                            key={item.href}
                            href={item.href}
                            className={cn("relative p-3 transition-colors", active ? "text-brand-500" : "text-slate-400")}
                        >
                            <item.icon className="w-6 h-6" />
                            {active && <span className="absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-brand-500 rounded-full" />}
                        </Link>
                    );
                })}
            </div>
        </div>
    );
}