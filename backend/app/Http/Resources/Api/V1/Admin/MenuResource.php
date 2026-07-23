<?php

namespace App\Http\Resources\Api\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'parent_id' => $this->parent_id,
            'canonical' => $this->canonical,
            'sort_order' => (int) $this->sort_order,
            'target' => $this->target,
            'status' => $this->status,
            'parent' => $this->whenLoaded('parent', fn (): array => [
                'id' => $this->parent->id,
                'title' => $this->parent->title,
                'canonical' => $this->parent->canonical,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
