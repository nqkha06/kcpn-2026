import { usePage } from '@inertiajs/react';
import {  useEffect, useRef } from 'react';
import type {ReactNode} from 'react';
import { toast } from 'sonner';
import AdminLayoutTemplate from '@/layouts/admin/admin-sidebar-layout';
import type {BreadcrumbItem} from '@/types';

interface AdminLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
    headerActions?: ReactNode;
}

export default ({ children, breadcrumbs, headerActions, ...props }: AdminLayoutProps) => {
    const page = usePage<any>();
    const previousFlash = useRef<any>(null);

    useEffect(() => {
        const flash = page.props.flash;

        // Skip if flash is the same as previous render
        if (JSON.stringify(flash) === JSON.stringify(previousFlash.current)) {
            return;
        }

        previousFlash.current = flash;

        if (flash?.success) {
            toast.success(flash.success, {
                position: 'top-right',
                style: {
                    background: '#22c55e',
                    color: '#fff'
                }
            });
        }

        if (flash?.error) {
            toast.error(flash.error, {
                position: 'top-right',
                style: {
                    background: '#ef4444',
                    color: '#fff'
                }
            });
        }

        if (flash?.info) {
            toast.info(flash.info, {
                position: 'top-right',
                style: {
                    background: '#3b82f6',
                    color: '#fff'
                }
            });
        }

        if (flash?.warning) {
            toast.warning(flash.warning, {
                position: 'top-right',
                style: {
                    background: '#f59e0b',
                    color: '#fff'
                }
            });
        }
    }, [page.props.flash]);

    return (
        <AdminLayoutTemplate breadcrumbs={breadcrumbs} headerActions={headerActions} {...props}>
            {children}
        </AdminLayoutTemplate>
    );
};
