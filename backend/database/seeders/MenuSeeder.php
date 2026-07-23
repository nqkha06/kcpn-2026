<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = [
            'home.header' => [
                [
                    'title' => 'Tính năng',
                    'url' => '/#features',
                    'target' => '_self',
                    'children' => [],
                ],
                [
                    'title' => 'Cách hoạt động',
                    'url' => '/#how-it-works',
                    'target' => '_self',
                    'children' => [],
                ],
                [
                    'title' => 'Câu hỏi thường gặp',
                    'url' => '/#templates',
                    'target' => '_self',
                    'children' => [],
                ],
                [
                    'title' => 'Liên hệ',
                    'url' => '/#contact',
                    'target' => '_self',
                    'children' => [],
                ],
            ],
            'home.footer' => [
                [
                    'title' => 'Sản phẩm',
                    'url' => null,
                    'target' => '_self',
                    'children' => [
                        ['title' => 'Cách hoạt động', 'url' => '/#how-it-works', 'target' => '_self'],
                        ['title' => 'Bảng xếp hạng', 'url' => '/leaderboard', 'target' => '_self'],
                    ],
                ],
                [
                    'title' => 'Công ty',
                    'url' => null,
                    'target' => '_self',
                    'children' => [
                        ['title' => 'Về chúng tôi', 'url' => '/about', 'target' => '_self'],
                        ['title' => 'Liên hệ', 'url' => '/#contact', 'target' => '_self'],
                    ],
                ],
                [
                    'title' => 'Hỗ trợ',
                    'url' => null,
                    'target' => '_self',
                    'children' => [
                        ['title' => 'Trung tâm trợ giúp', 'url' => '#', 'target' => '_self'],
                        ['title' => 'Chính sách bảo mật', 'url' => '#', 'target' => '_self'],
                    ],
                ],
            ],
            'user.header' => [
                [
                    'title' => 'Dashboard',
                    'url' => '/user/dashboard',
                    'target' => '_self',
                    'children' => [],
                ],
            ],
        ];

        foreach ($menus as $canonical => $items) {
            foreach ($items as $index => $item) {
                $parent = Menu::query()->updateOrCreate(
                    [
                        'canonical' => $canonical,
                        'parent_id' => null,
                        'title' => $item['title'],
                    ],
                    [
                        'url' => $item['url'],
                        'target' => $item['target'],
                        'status' => 'active',
                        'sort_order' => $index + 1,
                    ]
                );

                foreach ($item['children'] as $childIndex => $child) {
                    Menu::query()->updateOrCreate(
                        [
                            'canonical' => $canonical,
                            'parent_id' => $parent->id,
                            'title' => $child['title'],
                        ],
                        [
                            'url' => $child['url'],
                            'target' => $child['target'],
                            'status' => 'active',
                            'sort_order' => $childIndex + 1,
                        ]
                    );
                }
            }
        }
    }
}
