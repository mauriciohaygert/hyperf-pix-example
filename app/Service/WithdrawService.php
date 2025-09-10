<?php

declare(strict_types=1);

namespace App\Service;

use App\Domain\Command\WithdrawCommand;
use App\Domain\DTO\WithdrawRequestDTO;
use App\Domain\DTO\WithdrawResponseDTO;
use App\Domain\Event\WithdrawScheduled;
use App\Domain\Event\WithdrawProcessed;
use App\Model\AccountWithdraw;
use App\Model\AccountWithdrawPix;
use App\Repository\AccountRepository;
use App\Repository\AccountWithdrawRepository;
use App\Repository\AccountWithdrawPixRepository;
use Hyperf\DbConnection\Db;
use Psr\EventDispatcher\EventDispatcherInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class WithdrawService
{
    private LoggerInterface $logger;

    public function __construct(
        private AccountRepository $accountRepository,
        private AccountWithdrawRepository $withdrawRepository,
        private AccountWithdrawPixRepository $pixRepository,
        private EventDispatcherInterface $eventDispatcher,
        LoggerFactory $loggerFactory
    ) {
        $this->logger = $loggerFactory->get('withdraw');
    }

    public function processWithdraw(string $accountId, WithdrawRequestDTO $request): WithdrawResponseDTO
    {
        $command = new WithdrawCommand(
            accountId: $accountId,
            method: $request->method,
            amount: $request->amount,
            pixType: $request->getPixType(),
            pixKey: $request->getPixKey(),
            scheduled: $request->isScheduled(),
            scheduledFor: $request->getScheduledFor()
        );

        if (!$command->isValid()) {
            throw new \InvalidArgumentException('Invalid withdraw request: ' . implode(', ', $command->validate()));
        }

        $account = $this->accountRepository->findById($accountId);
        if (!$account) {
            throw new \InvalidArgumentException('Account not found');
        }

        return Db::transaction(function () use ($command, $account) {
            $withdraw = $this->withdrawRepository->create([
                'account_id' => $command->accountId,
                'method' => $command->method,
                'amount' => $command->amount,
                'scheduled' => $command->scheduled,
                'scheduled_for' => $command->scheduledFor,
                'done' => false,
                'error' => false,
            ]);

            $this->pixRepository->create([
                'account_withdraw_id' => $withdraw->id,
                'type' => $command->pixType,
                'key' => $command->pixKey,
            ]);

            $this->logger->info('Withdraw created', [
                'withdraw_id' => $withdraw->id,
                'account_id' => $command->accountId,
                'amount' => $command->amount,
                'scheduled' => $command->scheduled,
            ]);

            if ($command->isImmediate()) {
                $this->logger->info('Processing immediate withdraw', [
                    'withdraw_id' => $withdraw->id,
                ]);
                $this->processImmediateWithdraw($withdraw, $account);
            } else {
                $this->eventDispatcher->dispatch(new WithdrawScheduled($withdraw));
                $this->logger->info('Withdraw scheduled', [
                    'withdraw_id' => $withdraw->id,
                    'scheduled_for' => $command->scheduledFor->toISOString(),
                ]);
            }

            $withdraw->load('pix');
            return WithdrawResponseDTO::fromModel($withdraw);
        });
    }

    public function processScheduledWithdraws(): int
    {
        $pendingWithdraws = $this->withdrawRepository->findPendingScheduled();
        $processed = 0;

        foreach ($pendingWithdraws as $withdraw) {
            try {
                $account = $this->accountRepository->findById($withdraw->account_id);
                if (!$account) {
                    $this->markWithdrawAsError($withdraw, 'Account not found');
                    continue;
                }

                $this->processImmediateWithdraw($withdraw, $account);
                $processed++;

                $this->logger->info('Scheduled withdraw processed', [
                    'withdraw_id' => $withdraw->id,
                    'account_id' => $withdraw->account_id,
                    'amount' => $withdraw->amount,
                ]);

            } catch (\Exception $e) {
                $this->markWithdrawAsError($withdraw, $e->getMessage());
                $this->logger->error('Error processing scheduled withdraw', [
                    'withdraw_id' => $withdraw->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $processed;
    }

    private function processImmediateWithdraw(AccountWithdraw $withdraw, $account): void
    {
        $this->logger->info('Processing immediate withdraw', [
            'withdraw_id' => $withdraw->id,
            'account_id' => $account->id,
            'amount' => $withdraw->amount,
        ]);
        
        if (!$this->accountRepository->debitBalanceWithLock($account->id, (float) $withdraw->amount)) {
            $this->logger->info('Insufficient balance, marking as error', [
                'withdraw_id' => $withdraw->id,
            ]);
            $this->markWithdrawAsError($withdraw, 'Insufficient balance');
            return;
        }

        $this->logger->info('Balance debited successfully, marking as done', [
            'withdraw_id' => $withdraw->id,
        ]);
        
        $withdraw->markAsDone();

        $this->eventDispatcher->dispatch(new WithdrawProcessed($withdraw, true));
    }

    private function markWithdrawAsError(AccountWithdraw $withdraw, string $reason): void
    {
        $this->logger->info('Marking withdraw as error', [
            'withdraw_id' => $withdraw->id,
            'reason' => $reason,
        ]);
        
        $withdraw->markAsError($reason);
        
        $this->logger->info('Dispatching WithdrawProcessed event', [
            'withdraw_id' => $withdraw->id,
            'success' => false,
            'reason' => $reason,
        ]);
        
        $this->eventDispatcher->dispatch(new WithdrawProcessed($withdraw, false, $reason));
    }

    public function getWithdrawHistory(string $accountId, int $limit = 50): array
    {
        $withdraws = $this->withdrawRepository->getWithdrawHistory($accountId, $limit);
        
        return $withdraws->map(function (AccountWithdraw $withdraw) {
            return WithdrawResponseDTO::fromModel($withdraw);
        })->toArray();
    }
}
