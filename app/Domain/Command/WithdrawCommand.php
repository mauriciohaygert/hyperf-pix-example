<?php

declare(strict_types=1);

namespace App\Domain\Command;

use Carbon\Carbon;

class WithdrawCommand
{
    public function __construct(
        public readonly string $accountId,
        public readonly string $method,
        public readonly float $amount,
        public readonly string $pixType,
        public readonly string $pixKey,
        public readonly bool $scheduled = false,
        public readonly ?Carbon $scheduledFor = null
    ) {}

    public function isScheduled(): bool
    {
        return $this->scheduled && $this->scheduledFor !== null;
    }

    public function isImmediate(): bool
    {
        return !$this->isScheduled();
    }

    public function validate(): array
    {
        $errors = [];

        if (empty($this->accountId)) {
            $errors[] = 'Account ID is required';
        }

        if ($this->amount <= 0) {
            $errors[] = 'Amount must be greater than zero';
        }

        if ($this->method !== 'PIX') {
            $errors[] = 'Only PIX method is supported';
        }

        if ($this->pixType !== 'email') {
            $errors[] = 'Only email PIX keys are supported';
        }

        if (!filter_var($this->pixKey, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format for PIX key';
        }

        if ($this->isScheduled()) {
            if ($this->scheduledFor <= Carbon::now(\Hyperf\Config\config('app_timezone'))) {
                $errors[] = 'Scheduled date must be in the future';
            }

            if ($this->scheduledFor > Carbon::now(\Hyperf\Config\config('app_timezone'))->addDays(7)) {
                $errors[] = 'Cannot schedule more than 7 days in advance';
            }
        }

        return $errors;
    }

    public function isValid(): bool
    {
        return empty($this->validate());
    }
}
