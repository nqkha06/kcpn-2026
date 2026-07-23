<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Ăn uống', 'color' => '#f59e0b', 'description' => 'Nhà hàng, cà phê, đi chợ và đặt đồ ăn.'],
            ['name' => 'Di chuyển', 'color' => '#3b82f6', 'description' => 'Xăng xe, taxi, gửi xe và phương tiện công cộng.'],
            ['name' => 'Mua sắm', 'color' => '#ec4899', 'description' => 'Quần áo, đồ dùng cá nhân và mua hàng online.'],
            ['name' => 'Giải trí', 'color' => '#8b5cf6', 'description' => 'Phim ảnh, game, sự kiện và đăng ký dịch vụ.'],
            ['name' => 'Hóa đơn', 'color' => '#ef4444', 'description' => 'Điện, nước, internet và các khoản thanh toán định kỳ.'],
            ['name' => 'Sức khỏe', 'color' => '#14b8a6', 'description' => 'Khám bệnh, thuốc men, bảo hiểm và thể thao.'],
            ['name' => 'Gia đình', 'color' => '#f97316', 'description' => 'Chi phí sinh hoạt, con cái và người thân.'],
            ['name' => 'Giáo dục', 'color' => '#6366f1', 'description' => 'Khóa học, sách vở và học phí.'],
            ['name' => 'Du lịch', 'color' => '#06b6d4', 'description' => 'Vé máy bay, khách sạn và hoạt động nghỉ dưỡng.'],
            ['name' => 'Lương', 'color' => '#10b981', 'description' => 'Thu nhập chính hằng tháng.'],
            ['name' => 'Thưởng', 'color' => '#22c55e', 'description' => 'Thưởng hiệu suất, cashback và thu nhập phụ.'],
            ['name' => 'Đầu tư', 'color' => '#84cc16', 'description' => 'Cổ tức, lãi tiết kiệm và lợi nhuận đầu tư.'],
        ];

        foreach ($categories as $category) {
            Category::query()->firstOrCreate(
                ['name' => $category['name']],
                [
                    'color' => $category['color'],
                    'description' => $category['description'],
                    'status' => 'active',
                ],
            );
        }
    }
}
