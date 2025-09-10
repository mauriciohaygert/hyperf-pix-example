<?php

declare(strict_types=1);

namespace App\Domain\DTO;

use Carbon\Carbon;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class WithdrawRequestDTO
{
    private static ?LoggerInterface $logger = null;

    public function __construct(
        public readonly string $method,
        public readonly array $pix,
        public readonly float $amount,
        public readonly ?string $schedule = null
    ) {}

    private static function getLogger(): LoggerInterface
    {
        if (self::$logger === null) {
            $loggerFactory = \Hyperf\Context\ApplicationContext::getContainer()->get(LoggerFactory::class);
            self::$logger = $loggerFactory->get('withdraw-request-dto');
        }
        return self::$logger;
    }

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
            self::getLogger()->error('Failed to parse scheduled date', [
                'schedule' => $this->schedule,
                'timezone' => \Hyperf\Config\config('app_timezone'),
                'error' => $e->getMessage(),
            ]);
            
            // Lança exceção em vez de retornar null silenciosamente
            throw new \InvalidArgumentException(
                "Invalid scheduled date format: {$this->schedule}. Error: {$e->getMessage()}"
            );
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
