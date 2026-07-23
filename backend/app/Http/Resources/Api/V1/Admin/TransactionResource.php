<?php

namespace App\Http\Resources\Api\V1\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'wallet_id' => $this->wallet_id,
            'category_id' => $this->category_id,
            'type' => $this->type,
            'amount' => (float) $this->amount,
            'status' => $this->status,
            'note' => $this->note,
            'labels' => is_array($this->labels) ? $this->labels : [],
            'transacted_at' => $this->transacted_at?->format('Y-m-d'),
            'user' => $this->whenLoaded('user', fn (): array => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),
            'wallet' => $this->whenLoaded('wallet', fn (): array => [
                'id' => $this->wallet->id,
                'name' => $this->wallet->name,
                'currency' => $this->wallet->currency,
            ]),
            'category' => $this->whenLoaded('category', fn (): array => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'color' => $this->category->color,
            ]),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
