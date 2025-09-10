<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\AccountRepository;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

#[Controller]
class AccountController extends AbstractController
{
    #[Inject]
    protected AccountRepository $accountRepository;

    private LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('account-controller');
    }

    #[GetMapping(path: '/account/{accountId}')]
    public function getAccount(RequestInterface $request, ResponseInterface $response): \Psr\Http\Message\ResponseInterface
    {
        try {
            $accountId = $request->route('accountId');
            
            $account = $this->accountRepository->findById($accountId);
            
            if (!$account) {
                return $this->response->json([
                    'error' => 'Account not found',
                    'code' => 'ACCOUNT_NOT_FOUND'
                ])->withStatus(404);
            }

            return $this->response->json([
                'data' => [
                    'id' => $account->id,
                    'name' => $account->name,
                    'balance' => $account->balance,
                    'created_at' => $account->created_at->toISOString(),
                    'updated_at' => $account->updated_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error getting account', [
                'account_id' => $request->route('accountId'),
                'error' => $e->getMessage(),
            ]);

            return $this->response->json([
                'error' => 'Internal server error',
                'code' => 'INTERNAL_ERROR'
            ])->withStatus(500);
        }
    }

    #[GetMapping(path: '/account/{accountId}/balance')]
    public function getBalance(RequestInterface $request, ResponseInterface $response): \Psr\Http\Message\ResponseInterface
    {
        try {
            $accountId = $request->route('accountId');
            
            $account = $this->accountRepository->findById($accountId);
            
            if (!$account) {
                return $this->response->json([
                    'error' => 'Account not found',
                    'code' => 'ACCOUNT_NOT_FOUND'
                ])->withStatus(404);
            }

            return $this->response->json([
                'data' => [
                    'account_id' => $account->id,
                    'balance' => $account->balance,
                    'updated_at' => $account->updated_at->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error getting balance', [
                'account_id' => $request->route('accountId'),
                'error' => $e->getMessage(),
            ]);

            return $this->response->json([
                'error' => 'Internal server error',
                'code' => 'INTERNAL_ERROR'
            ])->withStatus(500);
        }
    }
}
