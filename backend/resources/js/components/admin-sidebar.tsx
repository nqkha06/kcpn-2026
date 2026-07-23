import { Link } from '@inertiajs/react';
import {
    Folder,
    Users,
    Settings,
    AlertTriangle,
    Shield,
    Key,
    Tags,
    PiggyBank,
    ReceiptText,
    ListTree,
    LayoutDashboard,
} from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/nav-main';
import { Button } from '@/components/ui/button';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import admin from '@/routes/admin';
import adminBudgets from '@/routes/admin/budgets';
import adminCategories from '@/routes/admin/categories';
import adminMenus from '@/routes/admin/menus';
import adminPages from '@/routes/admin/pages';
import adminPermissions from '@/routes/admin/permissions';
import adminRoles from '@/routes/admin/roles';
import adminAppearance from '@/routes/admin/settings/appearance/options';
import adminTransactions from '@/routes/admin/transactions';
import adminUsers from '@/routes/admin/users';
import type { NavItem } from '@/types';

const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: admin.dashboard().url,
        icon: LayoutDashboard,
    },
    {
        title: 'Users',
        icon: Users,
        children: [
            {
                title: 'Users',
                href: adminUsers.index().url,
                icon: Users,
            },
            {
                title: 'Roles',
                href: adminRoles.index().url,
                icon: Shield,
            },
            {
                title: 'Permissions',
                href: adminPermissions.index().url,
                icon: Key,
            },
        ],
    },

    {
        title: 'Categories',
        href: adminCategories.index().url,
        icon: Tags,
    },
    {
        title: 'Budgets',
        href: adminBudgets.index().url,
        icon: PiggyBank,
    },
    {
        title: 'Transactions',
        href: adminTransactions.index().url,
        icon: ReceiptText,
    },
    {
        title: 'Appearance',
        icon: Settings,
        children: [
            {
                title: 'Theme Options',
                href: adminAppearance.index().url,
                icon: Settings,
            },
        ],
    },
    {
        title: 'Pages',
        href: adminPages.index().url,
        icon: Folder,
    },
    {
        title: 'Menus',
        href: adminMenus.index().url,
        icon: ListTree,
    },
    // {
    //     title: 'Settings',
    //     icon: Settings,
    //     children: [{
    //         title: 'Languages',
    //         href: adminLanguages.index().url,
    //         icon: Languages,
    //     }],
    // },
];

export function AdminSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={admin.dashboard().url} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={adminNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <Button variant="outline" size="sm" className="w-full">
                    <a
                        href="/user"
                        target="_blank"
                        className="flex items-center"
                    >
                        <AlertTriangle className="mr-2 size-4" />
                        Go to User
                    </a>
                </Button>
            </SidebarFooter>
        </Sidebar>
    );
}
