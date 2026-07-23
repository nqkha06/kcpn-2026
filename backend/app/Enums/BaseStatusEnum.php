<?php

namespace App\Enums;

enum BaseStatusEnum: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
    case PENDING = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::PUBLISHED => __('Đã xuất bản'),
            self::DRAFT => __('Bản nháp'),
            self::PENDING => __('Chờ xử lý'),
            default => __('Unknown'),
        };
    }

    public function html(): string
    {
        return match ($this) {
            self::PUBLISHED => '<span class="badge bg-green text-green-fg">'.__('Xuất bản').'</span>',
            self::DRAFT => '<span class="badge bg-blue text-blue-fg">'.__('Bản nháp').'</span>',
            self::PENDING => '<span class="badge bg-yellow text-yellow-fg">'.__('Chờ xử lý').'</span>',
            default => '<span class="badge">'.__('Unknown').'</span>',
        };
    }
}
