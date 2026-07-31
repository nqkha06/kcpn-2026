<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $general = [
            'vi' => [
                'site_name' => 'Cashback',
                'site_title' => 'Cashback - Quản lý tài chính cá nhân',
                'tagline' => 'Kiểm soát chi tiêu, làm chủ tương lai',
                'meta_description' => 'Theo dõi giao dịch, quản lý ví và lập ngân sách cá nhân dễ dàng cùng Cashback.',
            ],
            'en' => [
                'site_name' => 'Cashback',
                'site_title' => 'Cashback - Personal Finance Management',
                'tagline' => 'Track spending and take control of your future',
                'meta_description' => 'Track transactions, manage wallets, and build personal budgets with Cashback.',
            ],
        ];

        $settings = [
            'appearance.logo_light' => '',
            'appearance.logo_dark' => '',
            'appearance.favicon' => '',
            'appearance.social_image' => '',
            'appearance.general' => json_encode($general, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ];

        foreach ($settings as $key => $value) {
            Setting::query()->firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
