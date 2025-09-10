<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Model\AccountWithdraw;

class WithdrawScheduled
{
    public function __construct(
        public readonly AccountWithdraw $withdraw
    ) {}
}
