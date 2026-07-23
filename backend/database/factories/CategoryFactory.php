<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            ['name' => 'Ăn uống', 'color' => '#f59e0b', 'description' => 'Nhà hàng, cà phê, đi chợ và đặt đồ ăn.'],
            ['name' => 'Di chuyển', 'color' => '#3b82f6', 'description' => 'Xăng xe, taxi, gửi xe và phương tiện công cộng.'],
            ['name' => 'Mua sắm', 'color' => '#ec4899', 'description' => 'Quần áo, đồ dùng cá nhân và mua hàng online.'],
            ['name' => 'Hóa đơn', 'color' => '#ef4444', 'description' => 'Điện, nước, internet và các khoản thanh toán định kỳ.'],
            ['name' => 'Giải trí', 'color' => '#8b5cf6', 'description' => 'Phim ảnh, game, sự kiện và đăng ký dịch vụ.'],
            ['name' => 'Sức khỏe', 'color' => '#14b8a6', 'description' => 'Khám bệnh, thuốc men, bảo hiểm và thể thao.'],
            ['name' => 'Lương', 'color' => '#10b981', 'description' => 'Thu nhập chính hằng tháng.'],
            ['name' => 'Thưởng', 'color' => '#22c55e', 'description' => 'Thưởng hiệu suất, cashback và thu nhập phụ.'],
        ];
        $category = $this->faker->randomElement($categories);

        return [
            'name' => $category['name'].' '.$this->faker->unique()->numberBetween(100, 99999),
            'color' => $category['color'],
            'description' => $category['description'],
            'status' => 'active',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'inactive',
        ]);
    }
}
