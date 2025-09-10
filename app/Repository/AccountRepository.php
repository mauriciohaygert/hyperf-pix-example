<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Account;
use Hyperf\DbConnection\Db;

class AccountRepository
{
    public function findById(string $id): ?Account
    {
        return Account::find($id);
    }

    public function findByIdWithLock(string $id): ?Account
    {
        return Account::lockForUpdate()->find($id);
    }

    public function create(array $data): Account
    {
        return Account::create($data);
    }

    public function updateBalance(string $accountId, float $newBalance): bool
    {
        return (bool) Account::where('id', $accountId)->update(['balance' => $newBalance]);
    }

    public function debitBalanceWithLock(string $accountId, float $amount): bool
    {
        return Db::transaction(function () use ($accountId, $amount) {
            $account = $this->findByIdWithLock($accountId);
            
            if (!$account || !$account->hasBalance($amount)) {
                return false;
            }

            return $account->debitBalance($amount);
        });
    }

    public function getAccountsWithBalance(): \Hyperf\Database\Model\Collection
    {
        return Account::where('balance', '>', 0)->get();
    }
}
