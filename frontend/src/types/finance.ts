export type TransactionType = "income" | "expense";
export type TransactionStatus = "posted" | "pending" | "cancelled";

export interface FinanceCategory {
  id: number;
  is_private: boolean;
  name: string;
  color: string | null;
  description: string | null;
  status: string;
}

export interface CategoryPayload {
  name: string;
  color: string;
  description: string | null;
}

export interface FinanceWallet {
  id: number;
  name: string;
  currency: string;
  opening_balance: number;
  current_balance: number;
  is_default: boolean;
  created_at: string | null;
  updated_at: string | null;
}

export interface TransactionRelation {
  id: number;
  name: string;
  currency?: string;
  color?: string | null;
}

export interface FinanceTransaction {
  id: number;
  wallet_id: number;
  category_id: number | null;
  type: TransactionType;
  amount: number;
  transacted_at: string;
  status: TransactionStatus;
  note: string | null;
  labels: string[];
  wallet?: TransactionRelation;
  category?: TransactionRelation | null;
  created_at: string | null;
  updated_at: string | null;
}

export interface UserDashboardData {
  categories: FinanceCategory[];
  wallets: FinanceWallet[];
  transactions: FinanceTransaction[];
}

export interface WalletPayload {
  name: string;
  currency: string;
  opening_balance: number;
  is_default: boolean;
}

export interface TransactionFilters {
  search?: string;
  type?: TransactionType;
  status?: "posted" | "pending";
  wallet_id?: number;
  category_id?: number;
  date_from?: string;
  date_to?: string;
  sort?: "transacted_at" | "amount" | "created_at";
  direction?: "asc" | "desc";
  page: number;
  per_page: number;
}

export interface TransactionPayload {
  wallet_id: number;
  category_id: number | null;
  type: TransactionType;
  amount: number;
  transacted_at: string;
  note: string | null;
  labels: string;
}

export type BudgetPeriod = "monthly" | "yearly";

export interface FinanceBudget {
  id: number;
  category_id: number;
  amount_limit: number;
  spent: number;
  period: BudgetPeriod;
  status: string;
  note: string | null;
  category: FinanceCategory;
  created_at: string | null;
  updated_at: string | null;
}

export interface BudgetPayload {
  category_id: number;
  amount_limit: number;
  period: BudgetPeriod;
  note: string | null;
}

export interface CurrencyOption {
  code: string;
  label: string;
}

export interface UserSettingsData {
  profile: {
    name: string;
    email: string;
  };
  preferences: {
    currency: string;
  };
  currency_options: CurrencyOption[];
}
