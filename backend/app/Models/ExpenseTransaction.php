<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseTransaction extends Model
{
    /** @use HasFactory<\Database\Factories\ExpenseTransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'category_id',
        'type',
        'amount',
        'transacted_at',
        'status',
        'note',
        'labels',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transacted_at' => 'date',
            'labels' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(UserWallet::class, 'wallet_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
