import { usePage } from '@inertiajs/react';

export default function TrackerFooter() {
    const page = usePage<any>();
    const siteName = page.props?.appearanceOptions?.site_name || 'Spendify';

    return (
        <footer className="border-t border-slate-200 bg-white">
            <div className="mx-auto flex w-full max-w-7xl flex-col items-center justify-between gap-4 px-4 py-6 text-sm text-slate-500 sm:flex-row sm:px-6">
                <p>{siteName} © {new Date().getFullYear()}</p>
                <div className="flex items-center gap-4">
                    <a href="#" className="hover:text-blue-700">
                        Quyền riêng tư
                    </a>
                    <a href="#" className="hover:text-blue-700">
                        Điều khoản
                    </a>
                    <a href="#" className="hover:text-blue-700">
                        Hỗ trợ
                    </a>
                </div>
            </div>
        </footer>
    );
}
