<?php

declare(strict_types=1);

namespace App\Domain\DTO;

use App\Model\AccountWithdraw;
use Carbon\Carbon;

class WithdrawResponseDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $accountId,
        public readonly string $method,
        public readonly float $amount,
        public readonly bool $scheduled,
        public readonly ?Carbon $scheduledFor,
        public readonly bool $done,
        public readonly bool $error,
        public readonly ?string $errorReason,
        public readonly array $pix,
        public readonly Carbon $createdAt
    ) {}

    public static function fromModel(AccountWithdraw $withdraw): self
    {
        $pixData = [
            'type' => $withdraw->pix?->type ?? '',
            'key' => $withdraw->pix?->key ?? '',
        ];
        
        return new self(
            id: $withdraw->id,
            accountId: $withdraw->account_id,
            method: $withdraw->method,
            amount: (float) $withdraw->amount,
            scheduled: (bool) $withdraw->scheduled,
            scheduledFor: $withdraw->scheduled_for,
            done: (bool) $withdraw->done,
            error: (bool) $withdraw->error,
            errorReason: $withdraw->error_reason,
            pix: $pixData,
            createdAt: $withdraw->created_at
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'account_id' => $this->accountId,
            'method' => $this->method,
            'amount' => $this->amount,
            'scheduled' => $this->scheduled,
            'scheduled_for' => $this->scheduledFor?->format('Y-m-d\TH:i:s'),
            'done' => $this->done,
            'error' => $this->error,
            'error_reason' => $this->errorReason,
            'pix' => $this->pix,
            'created_at' => $this->createdAt->format('Y-m-d\TH:i:s'),
        ];
    }
}
