import { usePage } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import { Sun, Moon } from 'lucide-react';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { Appearance} from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { useInitials } from '@/hooks/use-initials';
import type {BreadcrumbItem as BreadcrumbItemType, SharedData} from '@/types';

import { UserMenuContent } from './user-menu-content';

interface AdminHeaderProps {
    breadcrumbs?: BreadcrumbItemType[];
}

export function AdminHeader({ breadcrumbs = [] }: AdminHeaderProps) {
    const page = usePage<SharedData>();
    const { auth } = page.props;
    const getInitials = useInitials();

    const { appearance, updateAppearance } = useAppearance();

    const tabs: { value: Appearance; icon: LucideIcon; label: string }[] = [
        { value: 'light', icon: Sun, label: 'Light' },
        { value: 'dark', icon: Moon, label: 'Dark' },
    ];

    return (
        <header className="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/50 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4">
            <div className="flex items-center gap-2">
                <SidebarTrigger className="-ml-1" />
                <Breadcrumbs breadcrumbs={breadcrumbs} />
            </div>

            <div className="flex items-center gap-2">
                <div className="ml-auto flex items-center space-x-2">
                    <div className="relative flex items-center space-x-1">
                        {tabs.map((tab) => {
                            const Icon = tab.icon;
                            const isActive = appearance === tab.value;

                            if (!isActive) return null;

                            return (
                                <Button
                                    key={tab.value}
                                    variant="ghost"
                                    size="icon"
                                    className="group h-9 w-9 cursor-pointer"
                                    onClick={() => updateAppearance(appearance === 'light' ? 'dark' : 'light')}
                                    title={tab.label}
                                >
                                    <Icon className="!size-5 opacity-80 group-hover:opacity-100" />
                                </Button>
                            );
                        })}
                    </div>
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button
                                variant="ghost"
                                className="size-10 rounded-full p-1"
                            >
                                <Avatar className="size-8 overflow-hidden rounded-full">
                                    <AvatarImage
                                        src={auth.user.avatar}
                                        alt={auth.user.name}
                                    />
                                    <AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                        {getInitials(auth.user.name)}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent className="w-56" align="end">
                            <UserMenuContent user={auth.user} />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </header>
    );
}
