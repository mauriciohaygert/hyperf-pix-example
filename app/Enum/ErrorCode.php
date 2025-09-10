<?php

declare(strict_types=1);

namespace App\Enum;

enum ErrorCode: string
{
    case ACCOUNT_NOT_FOUND = 'ACCOUNT_NOT_FOUND';
    case WITHDRAW_NOT_FOUND = 'WITHDRAW_NOT_FOUND';
    case INSUFFICIENT_BALANCE = 'INSUFFICIENT_BALANCE';
    case INVALID_REQUEST = 'INVALID_REQUEST';
    case VALIDATION_ERROR = 'VALIDATION_ERROR';
    case SCHEDULE_TOO_FAR = 'SCHEDULE_TOO_FAR';
    case INTERNAL_ERROR = 'INTERNAL_ERROR';

    public function getMessage(): string
    {
        return match ($this) {
            self::ACCOUNT_NOT_FOUND => 'Account not found',
            self::WITHDRAW_NOT_FOUND => 'Withdraw not found',
            self::INSUFFICIENT_BALANCE => 'Insufficient balance',
            self::INVALID_REQUEST => 'Invalid request',
            self::VALIDATION_ERROR => 'Validation failed',
            self::SCHEDULE_TOO_FAR => 'Cannot schedule more than 7 days in advance',
            self::INTERNAL_ERROR => 'Internal server error',
        };
    }

    public function getHttpStatus(): int
    {
        return match ($this) {
            self::ACCOUNT_NOT_FOUND, self::WITHDRAW_NOT_FOUND => 404,
            self::INSUFFICIENT_BALANCE, self::INVALID_REQUEST, self::VALIDATION_ERROR, self::SCHEDULE_TOO_FAR => 422,
            self::INTERNAL_ERROR => 500,
        };
    }

    public function toJsonResponse(): array
    {
        return [
            'error' => $this->getMessage(),
            'code' => $this->value
        ];
    }

    public function getResponse(\Hyperf\HttpServer\Contract\ResponseInterface $response, array $details = []): \Psr\Http\Message\ResponseInterface
    {
        $data = $this->toJsonResponse();
        
        if (!empty($details)) {
            $data['details'] = $details;
        }
        
        return $response->json($data)->withStatus($this->getHttpStatus());
    }
}
