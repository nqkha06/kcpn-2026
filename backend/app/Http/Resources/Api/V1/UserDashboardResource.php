<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDashboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'categories' => CategoryResource::collection($this->resource['categories']),
            'wallets' => WalletResource::collection($this->resource['wallets']),
            'transactions' => TransactionResource::collection($this->resource['transactions']),
        ];
    }
}
