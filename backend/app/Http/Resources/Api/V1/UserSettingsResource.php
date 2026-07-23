<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'profile' => $this->resource['profile'],
            'preferences' => $this->resource['preferences'],
            'currency_options' => $this->resource['currency_options'],
        ];
    }
}
