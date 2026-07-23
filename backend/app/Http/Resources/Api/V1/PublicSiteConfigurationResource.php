<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicSiteConfigurationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'locale' => $this->resource['locale'],
            'default_locale' => $this->resource['default_locale'],
            'locales' => $this->resource['locales'],
            'can_register' => $this->resource['can_register'],
            'appearance' => $this->resource['appearance'],
            'menus' => [
                'home_header' => $this->resource['menus']['home.header'],
                'home_footer' => $this->resource['menus']['home.footer'],
                'user_header' => $this->resource['menus']['user.header'],
            ],
        ];
    }
}
