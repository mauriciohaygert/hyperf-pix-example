<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Model\AccountWithdraw;

class WithdrawProcessed
{
    public function __construct(
        public readonly AccountWithdraw $withdraw,
        public readonly bool $success,
        public readonly ?string $errorMessage = null
    ) {}
}
