<?php

namespace Database\Seeders;

use App\Enums\BaseStatusEnum;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::query()->where('email', 'admin@example.com')->first();

        foreach ($this->pages() as $page) {
            Page::query()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    ...$page,
                    'user_id' => $author?->id,
                    'status' => BaseStatusEnum::PUBLISHED->value,
                ],
            );
        }
    }

    /**
     * @return array<int, array{title: string, slug: string, content: string, meta_title: string, meta_description: string, meta_keywords: string, tags: array<int, string>}>
     */
    private function pages(): array
    {
        return [
            [
                'title' => 'Về chúng tôi',
                'slug' => 'gioi-thieu',
                'content' => '<h2>Quản lý tài chính đơn giản hơn</h2><p>Cashback giúp bạn theo dõi thu chi, quản lý nhiều ví và chủ động lập ngân sách trong một không gian thống nhất.</p><h2>Sứ mệnh</h2><p>Chúng tôi xây dựng các công cụ dễ sử dụng để mọi người hiểu rõ dòng tiền và đưa ra quyết định tài chính tốt hơn mỗi ngày.</p>',
                'meta_title' => 'Về chúng tôi | Cashback',
                'meta_description' => 'Tìm hiểu về Cashback và sứ mệnh giúp người dùng quản lý tài chính cá nhân hiệu quả.',
                'meta_keywords' => 'cashback, giới thiệu, quản lý tài chính',
                'tags' => ['company', 'about'],
            ],
            [
                'title' => 'Trung tâm trợ giúp',
                'slug' => 'trung-tam-tro-giup',
                'content' => '<h2>Bắt đầu sử dụng</h2><p>Tạo ví, thêm danh mục, ghi nhận giao dịch và thiết lập ngân sách tháng để bắt đầu theo dõi tài chính.</p><h2>Cần hỗ trợ thêm?</h2><p>Hãy gửi yêu cầu qua khu vực liên hệ trên trang chủ. Đội ngũ hỗ trợ sẽ phản hồi trong thời gian sớm nhất.</p>',
                'meta_title' => 'Trung tâm trợ giúp | Cashback',
                'meta_description' => 'Hướng dẫn bắt đầu và giải đáp các vấn đề thường gặp khi sử dụng Cashback.',
                'meta_keywords' => 'cashback, trợ giúp, hướng dẫn',
                'tags' => ['support', 'guide'],
            ],
            [
                'title' => 'Chính sách bảo mật',
                'slug' => 'chinh-sach-bao-mat',
                'content' => '<h2>Thông tin được thu thập</h2><p>Hệ thống chỉ xử lý những dữ liệu cần thiết để cung cấp tính năng quản lý tài chính và bảo vệ tài khoản của bạn.</p><h2>Bảo vệ dữ liệu</h2><p>Dữ liệu được kiểm soát truy cập và áp dụng các biện pháp bảo mật phù hợp. Chúng tôi không bán thông tin cá nhân của người dùng.</p>',
                'meta_title' => 'Chính sách bảo mật | Cashback',
                'meta_description' => 'Chính sách thu thập, sử dụng và bảo vệ dữ liệu người dùng của Cashback.',
                'meta_keywords' => 'cashback, bảo mật, quyền riêng tư',
                'tags' => ['legal', 'privacy'],
            ],
            [
                'title' => 'Điều khoản sử dụng',
                'slug' => 'dieu-khoan-su-dung',
                'content' => '<h2>Phạm vi sử dụng</h2><p>Cashback cung cấp công cụ hỗ trợ ghi nhận và phân tích tài chính cá nhân. Thông tin trên hệ thống không thay thế tư vấn tài chính chuyên nghiệp.</p><h2>Trách nhiệm người dùng</h2><p>Bạn có trách nhiệm bảo vệ thông tin đăng nhập, cung cấp dữ liệu chính xác và sử dụng dịch vụ đúng pháp luật.</p>',
                'meta_title' => 'Điều khoản sử dụng | Cashback',
                'meta_description' => 'Các điều khoản và trách nhiệm khi sử dụng nền tảng Cashback.',
                'meta_keywords' => 'cashback, điều khoản, sử dụng',
                'tags' => ['legal', 'terms'],
            ],
        ];
    }
}
