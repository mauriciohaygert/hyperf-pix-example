<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\AccountWithdrawPix;
use Hyperf\DbConnection\Db;

class AccountWithdrawPixRepository
{
    public function create(array $data): AccountWithdrawPix
    {
        $pix = new AccountWithdrawPix();
        
        foreach ($data as $key => $value) {
            $pix->{$key} = $value;
        }
        
        $pix->save();
        return $pix;
    }

    public function findById(string $id): ?AccountWithdrawPix
    {
        return AccountWithdrawPix::find($id);
    }

    public function findByWithdrawId(string $withdrawId): ?AccountWithdrawPix
    {
        return AccountWithdrawPix::where('account_withdraw_id', $withdrawId)->first();
    }
}
