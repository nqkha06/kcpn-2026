import type {PropsWithChildren} from 'react';
import { AdminHeader } from '@/components/admin-header';
import { AdminSidebar } from '@/components/admin-sidebar';
import { AppContent } from '@/components/app-content';
import { AppShell } from '@/components/app-shell';
import { Toaster } from '@/components/ui/sonner';
import type {BreadcrumbItem} from '@/types';

interface AdminSidebarLayoutProps {
    breadcrumbs?: BreadcrumbItem[];
}

export default function AdminSidebarLayout({
    children,
    breadcrumbs = [],
}: PropsWithChildren<AdminSidebarLayoutProps>) {
    return (
        <AppShell variant="sidebar">
            <AdminSidebar />
            <AppContent variant="sidebar" className="overflow-x-hidden">
                <AdminHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
            <Toaster />
        </AppShell>
    );
}
