<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Category;
use App\Models\ExpenseTransaction;
use App\Models\User;
use App\Models\UserWallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(CategorySeeder::class);

        $categories = Category::query()
            ->where('status', 'active')
            ->get()
            ->keyBy('name');

        foreach ($this->users() as $userData) {
            $user = User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'phone_number' => $userData['phone_number'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password'),
                ],
            );
            if ($user->roles()->doesntExist()) {
                $user->assignRole(Role::findOrCreate('user', 'web'));
            }

            $wallets = $this->seedWallets($user, $userData['wallets']);
            $this->seedBudgets($user, $categories);

            if ($user->expenseTransactions()->doesntExist()) {
                $this->seedTransactions($user, $wallets, $categories);
            }
        }
    }

    /**
     * @return array<int, array{name: string, first_name: string, last_name: string, phone_number: string, email: string, wallets: array<int, array{name: string, opening_balance: int, is_default: bool}>}>
     */
    private function users(): array
    {
        $users = [
            [
                'name' => 'Nguyễn Minh Anh',
                'first_name' => 'Minh Anh',
                'last_name' => 'Nguyễn',
                'phone_number' => '0901234567',
                'email' => 'test@example.com',
                'wallets' => [
                    ['name' => 'Ví tiền mặt', 'opening_balance' => 1_250_000, 'is_default' => true],
                    ['name' => 'Tài khoản ngân hàng', 'opening_balance' => 18_500_000, 'is_default' => false],
                    ['name' => 'Ví điện tử', 'opening_balance' => 750_000, 'is_default' => false],
                ],
            ],
            [
                'name' => 'Trần Quốc Huy',
                'first_name' => 'Quốc Huy',
                'last_name' => 'Trần',
                'phone_number' => '0912345678',
                'email' => 'huy.demo@example.com',
                'wallets' => [
                    ['name' => 'Ví tiền mặt', 'opening_balance' => 900_000, 'is_default' => true],
                    ['name' => 'Tài khoản ngân hàng', 'opening_balance' => 24_000_000, 'is_default' => false],
                    ['name' => 'Tiết kiệm', 'opening_balance' => 65_000_000, 'is_default' => false],
                ],
            ],
            [
                'name' => 'Lê Thảo Vy',
                'first_name' => 'Thảo Vy',
                'last_name' => 'Lê',
                'phone_number' => '0923456789',
                'email' => 'vy.demo@example.com',
                'wallets' => [
                    ['name' => 'Ví tiền mặt', 'opening_balance' => 650_000, 'is_default' => true],
                    ['name' => 'Tài khoản ngân hàng', 'opening_balance' => 14_200_000, 'is_default' => false],
                    ['name' => 'Ví điện tử', 'opening_balance' => 1_100_000, 'is_default' => false],
                ],
            ],
            [
                'name' => 'Phạm Gia Bảo',
                'first_name' => 'Gia Bảo',
                'last_name' => 'Phạm',
                'phone_number' => '0934567890',
                'email' => 'bao.demo@example.com',
                'wallets' => $this->wallets(1_400_000, 31_000_000, 1_800_000),
            ],
            [
                'name' => 'Hoàng Kim Chi',
                'first_name' => 'Kim Chi',
                'last_name' => 'Hoàng',
                'phone_number' => '0945678901',
                'email' => 'chi.demo@example.com',
                'wallets' => $this->wallets(820_000, 19_700_000, 900_000),
            ],
            [
                'name' => 'Đặng Tuấn Kiệt',
                'first_name' => 'Tuấn Kiệt',
                'last_name' => 'Đặng',
                'phone_number' => '0956789012',
                'email' => 'kiet.demo@example.com',
                'wallets' => $this->wallets(2_100_000, 42_500_000, 7_500_000, 'Tiết kiệm'),
            ],
            [
                'name' => 'Bùi Ngọc Linh',
                'first_name' => 'Ngọc Linh',
                'last_name' => 'Bùi',
                'phone_number' => '0967890123',
                'email' => 'linh.demo@example.com',
                'wallets' => $this->wallets(540_000, 12_800_000, 1_350_000),
            ],
            [
                'name' => 'Võ Đức Nam',
                'first_name' => 'Đức Nam',
                'last_name' => 'Võ',
                'phone_number' => '0978901234',
                'email' => 'nam.demo@example.com',
                'wallets' => $this->wallets(1_750_000, 28_300_000, 4_200_000, 'Tiết kiệm'),
            ],
            [
                'name' => 'Đỗ Thanh Tâm',
                'first_name' => 'Thanh Tâm',
                'last_name' => 'Đỗ',
                'phone_number' => '0989012345',
                'email' => 'tam.demo@example.com',
                'wallets' => $this->wallets(980_000, 16_400_000, 1_050_000),
            ],
            [
                'name' => 'Mai Anh Thư',
                'first_name' => 'Anh Thư',
                'last_name' => 'Mai',
                'phone_number' => '0990123456',
                'email' => 'thu.demo@example.com',
                'wallets' => $this->wallets(720_000, 22_900_000, 1_600_000),
            ],
            [
                'name' => 'Ngô Nhật Minh',
                'first_name' => 'Nhật Minh',
                'last_name' => 'Ngô',
                'phone_number' => '0902345678',
                'email' => 'minh.demo@example.com',
                'wallets' => $this->wallets(1_120_000, 37_600_000, 9_000_000, 'Tiết kiệm'),
            ],
            [
                'name' => 'Lý Hà My',
                'first_name' => 'Hà My',
                'last_name' => 'Lý',
                'phone_number' => '0913456789',
                'email' => 'my.demo@example.com',
                'wallets' => $this->wallets(680_000, 15_300_000, 1_240_000),
            ],
            [
                'name' => 'Cao Quang Vinh',
                'first_name' => 'Quang Vinh',
                'last_name' => 'Cao',
                'phone_number' => '0924567890',
                'email' => 'vinh.demo@example.com',
                'wallets' => $this->wallets(1_900_000, 45_200_000, 11_500_000, 'Tiết kiệm'),
            ],
        ];

        return $users;
    }

    /**
     * @return array<int, array{name: string, opening_balance: int, is_default: bool}>
     */
    private function wallets(int $cashBalance, int $bankBalance, int $thirdBalance, string $thirdWalletName = 'Ví điện tử'): array
    {
        return [
            ['name' => 'Ví tiền mặt', 'opening_balance' => $cashBalance, 'is_default' => true],
            ['name' => 'Tài khoản ngân hàng', 'opening_balance' => $bankBalance, 'is_default' => false],
            ['name' => $thirdWalletName, 'opening_balance' => $thirdBalance, 'is_default' => false],
        ];
    }

    /**
     * @param  array<int, array{name: string, opening_balance: int, is_default: bool}>  $wallets
     * @return \Illuminate\Support\Collection<int, UserWallet>
     */
    private function seedWallets(User $user, array $wallets): \Illuminate\Support\Collection
    {
        return collect($wallets)
            ->map(fn (array $wallet): UserWallet => UserWallet::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => $wallet['name'],
                ],
                [
                    'currency' => 'VND',
                    'opening_balance' => $wallet['opening_balance'],
                    'is_default' => $wallet['is_default'],
                ],
            ))
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<string, Category>  $categories
     */
    private function seedBudgets(User $user, \Illuminate\Support\Collection $categories): void
    {
        $budgets = [
            'Ăn uống' => 6_000_000,
            'Di chuyển' => 2_000_000,
            'Mua sắm' => 4_000_000,
            'Hóa đơn' => 3_500_000,
            'Giải trí' => 2_500_000,
            'Sức khỏe' => 2_000_000,
        ];

        foreach ($budgets as $categoryName => $amountLimit) {
            $category = $categories->get($categoryName);

            if (! $category instanceof Category) {
                continue;
            }

            Budget::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'period' => 'monthly',
                ],
                [
                    'amount_limit' => $amountLimit,
                    'status' => 'active',
                    'note' => 'Ngân sách demo theo dõi chi tiêu hằng tháng.',
                ],
            );
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, UserWallet>  $wallets
     * @param  \Illuminate\Support\Collection<string, Category>  $categories
     */
    private function seedTransactions(User $user, \Illuminate\Support\Collection $wallets, \Illuminate\Support\Collection $categories): void
    {
        $bankWallet = $wallets->firstWhere('name', 'Tài khoản ngân hàng') ?? $wallets->first();
        $cashWallet = $wallets->firstWhere('name', 'Ví tiền mặt') ?? $wallets->first();
        $digitalWallet = $wallets->firstWhere('name', 'Ví điện tử') ?? $cashWallet;

        for ($month = 0; $month < 6; $month++) {
            $salaryDate = Carbon::now()->startOfMonth()->subMonths($month)->addDays(4);

            $this->createTransaction($user, $bankWallet, $categories->get('Lương'), [
                'type' => 'income',
                'amount' => fake()->numberBetween(18_000_000, 32_000_000),
                'transacted_at' => $salaryDate,
                'note' => 'Lương tháng '.$salaryDate->format('m/Y'),
                'labels' => ['salary', 'recurring'],
            ]);

            if ($month % 2 === 0) {
                $this->createTransaction($user, $bankWallet, $categories->get('Thưởng'), [
                    'type' => 'income',
                    'amount' => fake()->numberBetween(500_000, 2_500_000),
                    'transacted_at' => $salaryDate->copy()->addDays(10),
                    'note' => 'Cashback và thưởng hiệu suất',
                    'labels' => ['bonus', 'cashback'],
                ]);
            }
        }

        $expenses = [
            ['Ăn uống', $cashWallet, 45_000, 450_000, 'Ăn uống trong ngày'],
            ['Di chuyển', $digitalWallet, 20_000, 300_000, 'Di chuyển'],
            ['Mua sắm', $bankWallet, 150_000, 2_800_000, 'Mua sắm online'],
            ['Hóa đơn', $bankWallet, 250_000, 1_800_000, 'Thanh toán hóa đơn'],
            ['Giải trí', $digitalWallet, 80_000, 900_000, 'Giải trí cuối tuần'],
            ['Sức khỏe', $bankWallet, 120_000, 1_500_000, 'Sức khỏe và thể thao'],
            ['Gia đình', $cashWallet, 100_000, 1_200_000, 'Chi phí gia đình'],
            ['Giáo dục', $bankWallet, 200_000, 2_000_000, 'Khóa học và sách'],
        ];

        foreach (range(1, 48) as $index) {
            [$categoryName, $wallet, $minAmount, $maxAmount, $note] = fake()->randomElement($expenses);

            $this->createTransaction($user, $wallet, $categories->get($categoryName), [
                'type' => 'expense',
                'amount' => fake()->numberBetween($minAmount, $maxAmount),
                'transacted_at' => Carbon::now()->subDays(fake()->numberBetween(0, 170)),
                'status' => $index % 12 === 0 ? 'pending' : 'posted',
                'note' => $note,
                'labels' => fake()->boolean(40) ? ['demo', fake()->randomElement(['online', 'family', 'recurring'])] : null,
            ]);
        }
    }

    /**
     * @param  array{type: string, amount: int, transacted_at: Carbon, status?: string, note: string, labels?: array<int, string>|null}  $attributes
     */
    private function createTransaction(User $user, ?UserWallet $wallet, ?Category $category, array $attributes): void
    {
        if (! $wallet instanceof UserWallet || ! $category instanceof Category) {
            return;
        }

        ExpenseTransaction::query()->create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'category_id' => $category->id,
            'type' => $attributes['type'],
            'amount' => $attributes['amount'],
            'transacted_at' => $attributes['transacted_at']->toDateString(),
            'status' => $attributes['status'] ?? 'posted',
            'note' => $attributes['note'],
            'labels' => $attributes['labels'] ?? null,
        ]);
    }
}
