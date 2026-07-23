export interface TrackerNavigationItem {
    label: string;
    href: string;
}

export interface TrackerProfile {
    name: string;
    email: string;
    initials: string;
}

export interface TrackerCategory {
    id: string;
    name: string;
    color: string;
}

export interface TrackerWallet {
    id: string;
    name: string;
    balance: number;
    openingBalance?: number;
    type: 'cash' | 'bank' | 'credit';
    currency?: string;
    isDefault?: boolean;
}

export interface TrackerTransaction {
    id: string;
    amount: number;
    type: 'income' | 'expense';
    categoryId: string;
    walletId: string;
    date: string;
    note?: string;
    labels?: string[];
    status?: 'posted' | 'pending' | 'cancelled';
}

export interface TrackerBudget {
    id: string;
    categoryId: string;
    limit: number;
    spent: number;
    period: 'monthly' | 'yearly';
}

export interface TrackerDataBundle {
    categories: TrackerCategory[];
    wallets: TrackerWallet[];
    transactions: TrackerTransaction[];
    budgets: TrackerBudget[];
}
