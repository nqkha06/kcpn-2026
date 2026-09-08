<?php

namespace App\Enums;

enum BaseStatusEnum: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
    case PENDING = 'pending';

    public function label(): string
    {
        $labels = [
            self::PUBLISHED->value => __('Đã xuất bản'),
            self::DRAFT->value => __('Bản nháp'),
            self::PENDING->value => __('Chờ xử lý'),
        ];

        return $labels[$this->value];
    }

    public function html(): string
    {
        $badges = [
            self::PUBLISHED->value => '<span class="badge bg-green text-green-fg">'.__('Xuất bản').'</span>',
            self::DRAFT->value => '<span class="badge bg-blue text-blue-fg">'.__('Bản nháp').'</span>',
            self::PENDING->value => '<span class="badge bg-yellow text-yellow-fg">'.__('Chờ xử lý').'</span>',
        ];

        return $badges[$this->value];
    }
}
