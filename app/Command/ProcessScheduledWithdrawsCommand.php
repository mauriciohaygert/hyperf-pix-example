<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\WithdrawService;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Command\Annotation\Command;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

#[Command]
class ProcessScheduledWithdrawsCommand extends HyperfCommand
{
    #[Inject]
    protected WithdrawService $withdrawService;

    private LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        parent::__construct('withdraw:process-scheduled');
        $this->logger = $loggerFactory->get('cron');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Process scheduled withdraws that are ready to be executed');
    }

    public function handle()
    {
        $this->info('Starting scheduled withdraws processing...');
        
        try {
            $processed = $this->withdrawService->processScheduledWithdraws();
            
            $this->info("Processed {$processed} scheduled withdraws");
            
            $this->logger->info('Scheduled withdraws processed', [
                'processed_count' => $processed,
            ]);

            return 0;

        } catch (\Exception $e) {
            $this->error('Error processing scheduled withdraws: ' . $e->getMessage());
            
            $this->logger->error('Error processing scheduled withdraws', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }
}
