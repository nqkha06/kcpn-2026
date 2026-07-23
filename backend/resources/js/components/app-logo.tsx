import { usePage } from '@inertiajs/react';
import AppLogoIcon from '@/components/app-logo-icon';
import { useAppearance } from '@/hooks/use-appearance';

export default function AppLogo() {
    const page = usePage<any>();
    const { resolvedAppearance } = useAppearance();
    const appearanceOptions = page.props?.appearanceOptions ?? {};

    const logoUrl =
        resolvedAppearance === 'dark'
            ? appearanceOptions.logo_dark || appearanceOptions.logo_light
            : appearanceOptions.logo_light || appearanceOptions.logo_dark;

    const siteName = appearanceOptions.site_name || 'Spendify';

    return (
        <>
            <div className="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                {logoUrl ? (
                    <img
                        src={logoUrl}
                        alt={siteName}
                        className="size-6 rounded-sm object-contain"
                    />
                ) : (
                    <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
                )}
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="mb-0.5 truncate leading-tight font-semibold">
                    {siteName}
                </span>
            </div>
        </>
    );
}
