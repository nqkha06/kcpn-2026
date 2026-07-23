import { apiClient } from "@/lib/api/client";
import type { ApiSuccess } from "@/types/api";
import type { FinanceWallet, WalletPayload } from "@/types/finance";

export const walletQueryKey = ["user", "wallets"] as const;

export const walletService = {
  list: async (): Promise<FinanceWallet[]> => {
    const response = await apiClient.get<ApiSuccess<FinanceWallet[]>>("/user/wallets");

    return response.data;
  },
  create: async (payload: WalletPayload): Promise<FinanceWallet> => {
    const response = await apiClient.post<ApiSuccess<FinanceWallet>>("/user/wallets", payload);

    return response.data;
  },
  update: async (walletId: number, payload: WalletPayload): Promise<FinanceWallet> => {
    const response = await apiClient.patch<ApiSuccess<FinanceWallet>>(
      `/user/wallets/${walletId}`,
      payload,
    );

    return response.data;
  },
  destroy: async (walletId: number): Promise<void> => {
    await apiClient.delete<ApiSuccess<null>>(`/user/wallets/${walletId}`);
  },
};
