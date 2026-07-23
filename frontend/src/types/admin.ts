export interface AdminDashboardStats {
    users: number;
    wallets: number;
    activeCategories: number;
    activeBudgets: number;
    postedIncomeThisMonth: number;
    postedExpenseThisMonth: number;
    netThisMonth: number;
    pendingTransactions: number;
}

export interface AdminMonthlyFlowItem {
    month: string;
    income: number;
    expense: number;
    net: number;
}

export interface AdminTopExpenseCategory {
    id: number;
    name: string;
    color: string;
    amount: number;
}

export interface AdminRecentTransaction {
    id: number;
    type: "income" | "expense";
    amount: number;
    status: "posted" | "pending" | "cancelled";
    transactedAt: string | null;
    userName: string | null;
    walletName: string | null;
    currency: string | null;
    categoryName: string | null;
    categoryColor: string | null;
}

export interface AdminDashboardData {
    stats: AdminDashboardStats;
    monthlyFlow: AdminMonthlyFlowItem[];
    topExpenseCategories: AdminTopExpenseCategory[];
    recentTransactions: AdminRecentTransaction[];
}

export interface AdminPermission {
    id: number;
    name: string;
    guard_name: string;
    roles_count?: number;
    created_at: string | null;
    updated_at: string | null;
}

export interface AdminRole {
    id: number;
    name: string;
    guard_name: string;
    permissions: AdminPermission[];
    created_at: string | null;
    updated_at: string | null;
}

export interface AdminUser {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    roles: AdminRole[];
    created_at: string | null;
    updated_at: string | null;
}

export interface AdminUserPayload {
    name: string;
    email: string;
    password?: string;
    password_confirmation?: string;
    roles: number[];
}

export interface AdminRolePayload {
    name: string;
    permissions: number[];
}

export interface AdminPermissionPayload {
    name: string;
}

export interface AdminCategory {
    id: number;
    name: string;
    color: string;
    description: string | null;
    status: "active" | "inactive";
    created_at: string | null;
    updated_at: string | null;
}

export interface AdminCategoryPayload {
    name: string;
    color: string;
    description: string | null;
    status: "active" | "inactive";
}

export interface AdminOptionUser {
    id: number;
    name: string;
    email: string;
}

export interface AdminOptionWallet {
    id: number;
    user_id: number;
    name: string;
    currency: string;
    user_name: string | null;
}

export interface AdminOptionCategory {
  id: number;
  user_id?: number | null;
  name: string;
    color: string;
    status?: string;
}

export interface AdminTransactionOptions {
    users: AdminOptionUser[];
    wallets: AdminOptionWallet[];
    categories: AdminOptionCategory[];
    types: Array<"income" | "expense">;
    statuses: Array<"posted" | "pending" | "cancelled">;
}

export interface AdminTransaction {
    id: number;
    user_id: number;
    wallet_id: number;
    category_id: number | null;
    type: "income" | "expense";
    amount: number;
    status: "posted" | "pending" | "cancelled";
    note: string | null;
    labels: string[];
    transacted_at: string | null;
    user: AdminOptionUser | null;
    wallet: Pick<AdminOptionWallet, "id" | "name" | "currency"> | null;
    category: AdminOptionCategory | null;
    created_at: string | null;
    updated_at: string | null;
}

export interface AdminTransactionPayload {
    user_id: number;
    wallet_id: number;
    category_id: number | null;
    type: "income" | "expense";
    amount: number;
    transacted_at: string;
    status: "posted" | "pending" | "cancelled";
    note: string | null;
    labels: string;
}

export interface AdminBudgetOptions {
    users: AdminOptionUser[];
    categories: AdminOptionCategory[];
    periods: Array<"monthly" | "yearly">;
    statuses: Array<"active" | "inactive">;
}

export interface AdminBudget {
    id: number;
    user_id: number;
    category_id: number;
    amount_limit: number;
    spent: number;
    period: "monthly" | "yearly";
    status: "active" | "inactive";
    note: string | null;
    user: AdminOptionUser | null;
    category: AdminOptionCategory | null;
    created_at: string | null;
    updated_at: string | null;
}

export interface AdminBudgetPayload {
    user_id: number;
    category_id: number;
    amount_limit: number;
    period: "monthly" | "yearly";
    status: "active" | "inactive";
    note: string | null;
}

export interface AdminPage {
    id: number;
    user_id: number | null;
    category_id: number | null;
    title: string;
    slug: string;
    image: string | null;
    content: string | null;
    meta_title: string | null;
    meta_description: string | null;
    meta_keywords: string | null;
    tags: string[];
    status: "published" | "draft" | "pending";
    category: { id: number; name: string } | null;
    author: AdminOptionUser | null;
    created_at: string | null;
    updated_at: string | null;
}

export interface AdminPagePayload {
    title: string;
    slug: string | null;
    image: string | null;
    content: string | null;
    meta_title: string | null;
    meta_description: string | null;
    meta_keywords: string | null;
    tags: string;
    status: "published" | "draft" | "pending";
}

export interface AdminMenu {
    id: number;
    title: string;
    url: string | null;
    parent_id: number | null;
    canonical: string;
    sort_order: number;
    target: "_self" | "_blank";
    status: "active" | "inactive";
    parent: Pick<AdminMenu, "id" | "title" | "canonical"> | null;
    created_at: string | null;
    updated_at: string | null;
}

export interface AdminMenuPayload {
    title: string;
    url: string | null;
    parent_id: number | null;
    canonical: string;
    sort_order: number;
    target: "_self" | "_blank";
    status: "active" | "inactive";
}

export interface AdminAppearanceLanguage {
    id: number;
    name: string;
    code: string;
    locale: string;
    is_default: boolean;
}

export interface AdminAppearanceGeneralEntry {
    site_name?: string;
    site_title?: string;
    tagline?: string;
    meta_description?: string;
}

export interface AdminAppearanceData {
    languages: AdminAppearanceLanguage[];
    logos: Record<
        "logo_light" | "logo_dark" | "favicon" | "social_image",
        { path: string | null; url: string | null }
    >;
    general: Record<string, AdminAppearanceGeneralEntry>;
}
