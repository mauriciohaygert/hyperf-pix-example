<?php

declare(strict_types=1);

namespace App\Listener;

use App\Domain\Event\WithdrawProcessed;
use App\Domain\Event\WithdrawScheduled;
use App\Service\EmailService;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

#[Listener]
class WithdrawEventListener implements ListenerInterface
{
    private LoggerInterface $logger;
    private static array $processedEvents = [];

    public function __construct(
        private EmailService $emailService,
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
        $eventKey = $this->getEventKey($event);
        
        if (isset(self::$processedEvents[$eventKey])) {
            $this->logger->info('Event already processed, skipping', [
                'event_key' => $eventKey,
                'event_type' => get_class($event),
            ]);
            return;
        }
        
        self::$processedEvents[$eventKey] = true;
        
        if ($event instanceof WithdrawProcessed) {
            $this->processEmailNotification($event);
        } elseif ($event instanceof WithdrawScheduled) {
            $this->processScheduledEmailNotification($event);
        }
    }

    private function processEmailNotification(WithdrawProcessed $event): void
    {
        try {
            $this->emailService->sendWithdrawNotification($event->withdraw, $event->success, $event->errorMessage);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send email notification', [
                'withdraw_id' => $event->withdraw->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function processScheduledEmailNotification(WithdrawScheduled $event): void
    {
        $this->logger->info('Event received - processing scheduled email notification', [
            'withdraw_id' => $event->withdraw->id,
            'event_type' => 'App\\Domain\\Event\\WithdrawScheduled',
        ]);
        
        try {
            $this->emailService->sendScheduledWithdrawNotification($event->withdraw);
            $this->logger->info('Scheduled email notification sent successfully', [
                'withdraw_id' => $event->withdraw->id,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send scheduled withdraw email notification', [
                'withdraw_id' => $event->withdraw->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getEventKey(object $event): string
    {
        if ($event instanceof WithdrawProcessed) {
            return 'processed_' . $event->withdraw->id . '_' . ($event->success ? 'success' : 'error');
        } elseif ($event instanceof WithdrawScheduled) {
            return 'scheduled_' . $event->withdraw->id;
        }
        
        return 'unknown_' . get_class($event) . '_' . spl_object_hash($event);
    }
}
