<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\AccountWithdraw;
use Carbon\Carbon;
use Hyperf\Database\Model\Collection;

class AccountWithdrawRepository
{
    public function findById(string $id): ?AccountWithdraw
    {
        return AccountWithdraw::with(['account', 'pix'])->find($id);
    }

    public function create(array $data): AccountWithdraw
    {
        $withdraw = new AccountWithdraw();
        
        foreach ($data as $key => $value) {
            $withdraw->{$key} = $value;
        }
        
        $withdraw->save();
        return $withdraw;
    }

    public function findPendingScheduled(): Collection
    {
        return AccountWithdraw::with(['account', 'pix'])
            ->where('scheduled', true)
            ->where('done', false)
            ->where('scheduled_for', '<=', Carbon::now())
            ->get();
    }

    public function findByAccountId(string $accountId): Collection
    {
        return AccountWithdraw::with(['pix'])
            ->where('account_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getWithdrawHistory(string $accountId, int $limit = 50): Collection
    {
        return AccountWithdraw::with(['pix'])
            ->where('account_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getTotalWithdrawnToday(string $accountId): float
    {
        $today = Carbon::today();
        
        return AccountWithdraw::where('account_id', $accountId)
            ->where('done', true)
            ->where('error', false)
            ->whereDate('created_at', $today)
            ->sum('amount');
    }

    public function markAsProcessed(string $id, bool $success = true, ?string $errorReason = null): bool
    {
        $withdraw = AccountWithdraw::find($id);
        
        if (!$withdraw) {
            return false;
        }

        if ($success) {
            return $withdraw->markAsDone();
        }
        return $withdraw->markAsError($errorReason ?? 'Unknown error');
    }
}
