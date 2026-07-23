<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserWallet extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'currency',
        'opening_balance',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenseTransactions(): HasMany
    {
        return $this->hasMany(ExpenseTransaction::class, 'wallet_id');
    }

    public function scopeWithPostedTransactionTotals(Builder $query): Builder
    {
        return $query
            ->withSum([
                'expenseTransactions as posted_income_total' => fn (Builder $query) => $query
                    ->where('status', 'posted')
                    ->where('type', 'income'),
            ], 'amount')
            ->withSum([
                'expenseTransactions as posted_expense_total' => fn (Builder $query) => $query
                    ->where('status', 'posted')
                    ->where('type', 'expense'),
            ], 'amount');
    }

    public function currentBalance(): float
    {
        $postedIncomeTotal = $this->posted_income_total ?? $this->expenseTransactions()
            ->where('status', 'posted')
            ->where('type', 'income')
            ->sum('amount');

        $postedExpenseTotal = $this->posted_expense_total ?? $this->expenseTransactions()
            ->where('status', 'posted')
            ->where('type', 'expense')
            ->sum('amount');

        return (float) $this->opening_balance
            + (float) $postedIncomeTotal
            - (float) $postedExpenseTotal;
    }
}
