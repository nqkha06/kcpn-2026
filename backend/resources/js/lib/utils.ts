import type { InertiaLinkProps } from '@inertiajs/react';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

export function resolveCurrencyCode(currency?: string | null): string | undefined {
    if (! currency) {
        return undefined;
    }

    const normalizedCurrency = currency.trim().toUpperCase();

    if (normalizedCurrency.length !== 3) {
        return undefined;
    }

    return normalizedCurrency;
}

export function formatCurrencyAmount(
    amount: number,
    currency?: string,
    minimumFractionDigits = 2,
): string {
    const resolvedCurrency = resolveCurrencyCode(currency);

    if (resolvedCurrency !== undefined) {
        try {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: resolvedCurrency,
                minimumFractionDigits,
                maximumFractionDigits: minimumFractionDigits,
            }).format(amount);
        } catch {
            // Fall through to decimal formatting when currency code is invalid.
        }
    }

    return new Intl.NumberFormat('vi-VN', {
        minimumFractionDigits,
        maximumFractionDigits: minimumFractionDigits,
    }).format(amount);
}
