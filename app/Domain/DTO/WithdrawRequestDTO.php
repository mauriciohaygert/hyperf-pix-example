<?php

declare(strict_types=1);

namespace App\Domain\DTO;

use Carbon\Carbon;

class WithdrawRequestDTO
{
    public function __construct(
        public readonly string $method,
        public readonly array $pix,
        public readonly float $amount,
        public readonly ?string $schedule = null
    ) {}

    public function getPixType(): string
    {
        return $this->pix['type'] ?? '';
    }

    public function getPixKey(): string
    {
        return $this->pix['key'] ?? '';
    }

    public function isScheduled(): bool
    {
        return !empty($this->schedule);
    }

    public function getScheduledFor(): ?Carbon
    {
        if (!$this->isScheduled()) {
            return null;
        }

        try {
            return Carbon::parse($this->schedule, \Hyperf\Config\config('app_timezone'));
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function fromArray(array $data): self
    {
        return new self(
            method: $data['method'] ?? '',
            pix: $data['pix'] ?? [],
            amount: (float) ($data['amount'] ?? 0),
            schedule: $data['schedule'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'method' => $this->method,
            'pix' => $this->pix,
            'amount' => $this->amount,
            'schedule' => $this->schedule,
        ];
    }
}
