<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
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
            'category_id' => $this->category_id,
            'amount_limit' => (float) $this->amount_limit,
            'spent' => (float) ($this->getAttribute('spent') ?? 0),
            'period' => $this->period,
            'status' => $this->status,
            'note' => $this->note,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
