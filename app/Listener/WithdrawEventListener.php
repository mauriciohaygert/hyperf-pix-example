<?php

declare(strict_types=1);

namespace App\Listener;

use App\Domain\Event\WithdrawProcessed;
use App\Domain\Event\WithdrawScheduled;
use App\Service\EmailService;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Redis\Redis;
use Psr\Log\LoggerInterface;

#[Listener]
class WithdrawEventListener implements ListenerInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private EmailService $emailService,
        private Redis $redis,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->get('withdraw-listener');
    }

    public function listen(): array
    {
        return [
            WithdrawProcessed::class,
            WithdrawScheduled::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof WithdrawProcessed) {
            $this->processEmailNotification($event);
        } elseif ($event instanceof WithdrawScheduled) {
            $this->processScheduledEmailNotification($event);
        }
    }

    private function processEmailNotification(WithdrawProcessed $event): void
    {
        $eventKey = 'email_sent:' . $event->withdraw->id . '_' . ($event->success ? 'success' : 'error');
        
        if ($this->redis->exists($eventKey)) {
            $this->logger->info('Email notification already sent, skipping duplicate', [
                'withdraw_id' => $event->withdraw->id,
                'event_key' => $eventKey,
            ]);
            return;
        }
        $this->redis->setex($eventKey, 3600, '1');
        
        try {
            $this->emailService->sendWithdrawNotification($event->withdraw, $event->success, $event->errorMessage);
            $this->logger->info('Email notification sent successfully', [
                'withdraw_id' => $event->withdraw->id,
                'event_key' => $eventKey,
            ]);
        } catch (\Exception $e) {
            $this->redis->del($eventKey);
            $this->logger->error('Failed to send email notification', [
                'withdraw_id' => $event->withdraw->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function processScheduledEmailNotification(WithdrawScheduled $event): void
    {
        $eventKey = 'email_sent:' . $event->withdraw->id . '_scheduled';
        
        if ($this->redis->exists($eventKey)) {
            $this->logger->info('Scheduled email notification already sent, skipping duplicate', [
                'withdraw_id' => $event->withdraw->id,
                'event_key' => $eventKey,
            ]);
            return;
        }
        $this->redis->setex($eventKey, 3600, '1');
        
        $this->logger->info('Event received - processing scheduled email notification', [
            'withdraw_id' => $event->withdraw->id,
            'event_type' => 'App\\Domain\\Event\\WithdrawScheduled',
        ]);
        
        try {
            $this->emailService->sendScheduledWithdrawNotification($event->withdraw);
            $this->logger->info('Scheduled email notification sent successfully', [
                'withdraw_id' => $event->withdraw->id,
                'event_key' => $eventKey,
            ]);
        } catch (\Exception $e) {
            $this->redis->del($eventKey);
            $this->logger->error('Failed to send scheduled withdraw email notification', [
                'withdraw_id' => $event->withdraw->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
