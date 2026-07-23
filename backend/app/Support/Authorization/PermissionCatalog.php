<?php

namespace App\Support\Authorization;

final class PermissionCatalog
{
    public const GUARD = 'web';

    public const ADMIN_ROLE = 'admin';

    public const USER_ROLE = 'user';

    /**
     * @var array<string, array{label: string, scope: 'admin'|'user', permissions: list<string>}>
     */
    private const MODULES = [
        'admin.dashboard' => [
            'label' => 'Bảng điều khiển quản trị',
            'scope' => 'admin',
            'permissions' => ['admin.dashboard.view'],
        ],
        'admin.appearance' => [
            'label' => 'Giao diện hệ thống',
            'scope' => 'admin',
            'permissions' => ['admin.appearance.view', 'admin.appearance.update'],
        ],
        'admin.users' => [
            'label' => 'Người dùng',
            'scope' => 'admin',
            'permissions' => [
                'admin.users.view',
                'admin.users.create',
                'admin.users.update',
                'admin.users.delete',
            ],
        ],
        'admin.roles' => [
            'label' => 'Vai trò',
            'scope' => 'admin',
            'permissions' => [
                'admin.roles.view',
                'admin.roles.create',
                'admin.roles.update',
                'admin.roles.delete',
            ],
        ],
        'admin.permissions' => [
            'label' => 'Quyền',
            'scope' => 'admin',
            'permissions' => [
                'admin.permissions.view',
                'admin.permissions.create',
                'admin.permissions.update',
                'admin.permissions.delete',
            ],
        ],
        'admin.pages' => [
            'label' => 'Trang nội dung',
            'scope' => 'admin',
            'permissions' => [
                'admin.pages.view',
                'admin.pages.create',
                'admin.pages.update',
                'admin.pages.delete',
            ],
        ],
        'admin.menus' => [
            'label' => 'Menu',
            'scope' => 'admin',
            'permissions' => [
                'admin.menus.view',
                'admin.menus.create',
                'admin.menus.update',
                'admin.menus.delete',
            ],
        ],
        'admin.categories' => [
            'label' => 'Danh mục hệ thống',
            'scope' => 'admin',
            'permissions' => [
                'admin.categories.view',
                'admin.categories.create',
                'admin.categories.update',
                'admin.categories.delete',
            ],
        ],
        'admin.budgets' => [
            'label' => 'Ngân sách',
            'scope' => 'admin',
            'permissions' => [
                'admin.budgets.view',
                'admin.budgets.create',
                'admin.budgets.update',
                'admin.budgets.delete',
            ],
        ],
        'admin.transactions' => [
            'label' => 'Giao dịch',
            'scope' => 'admin',
            'permissions' => [
                'admin.transactions.view',
                'admin.transactions.create',
                'admin.transactions.update',
                'admin.transactions.delete',
            ],
        ],
        'admin.languages' => [
            'label' => 'Ngôn ngữ',
            'scope' => 'admin',
            'permissions' => [
                'admin.languages.view',
                'admin.languages.create',
                'admin.languages.update',
                'admin.languages.delete',
            ],
        ],
        'user.dashboard' => [
            'label' => 'Bảng điều khiển cá nhân',
            'scope' => 'user',
            'permissions' => ['user.dashboard.view'],
        ],
        'user.wallets' => [
            'label' => 'Ví cá nhân',
            'scope' => 'user',
            'permissions' => [
                'user.wallets.view',
                'user.wallets.create',
                'user.wallets.update',
                'user.wallets.delete',
            ],
        ],
        'user.categories' => [
            'label' => 'Danh mục cá nhân',
            'scope' => 'user',
            'permissions' => [
                'user.categories.view',
                'user.categories.create',
                'user.categories.update',
                'user.categories.delete',
            ],
        ],
        'user.transactions' => [
            'label' => 'Giao dịch cá nhân',
            'scope' => 'user',
            'permissions' => ['user.transactions.view', 'user.transactions.create'],
        ],
        'user.budgets' => [
            'label' => 'Ngân sách cá nhân',
            'scope' => 'user',
            'permissions' => ['user.budgets.view', 'user.budgets.create'],
        ],
        'user.settings' => [
            'label' => 'Cài đặt cá nhân',
            'scope' => 'user',
            'permissions' => ['user.settings.view', 'user.settings.update'],
        ],
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return array_values(array_unique(array_merge(...array_column(self::MODULES, 'permissions'))));
    }

    /**
     * @return list<string>
     */
    public static function forRole(string $role): array
    {
        return match ($role) {
            self::ADMIN_ROLE => self::all(),
            self::USER_ROLE => self::forScope('user'),
            default => [],
        };
    }

    /**
     * @return array<string, array{label: string, scope: 'admin'|'user', permissions: list<string>}>
     */
    public static function modules(): array
    {
        return self::MODULES;
    }

    /**
     * @return list<string>
     */
    private static function forScope(string $scope): array
    {
        $modules = array_filter(
            self::MODULES,
            static fn (array $module): bool => $module['scope'] === $scope,
        );

        return array_values(array_merge(...array_column($modules, 'permissions')));
    }
}
