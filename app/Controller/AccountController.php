<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\ErrorCode;
use App\Repository\AccountRepository;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
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
    public function getAccount(): \Psr\Http\Message\ResponseInterface
    {
        try {
            $accountId = $this->request->route('accountId');
            
            $account = $this->accountRepository->findById($accountId);
            
            if (!$account) {
                $errorCode = ErrorCode::ACCOUNT_NOT_FOUND;
                return $errorCode->getResponse($this->response);
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
                'account_id' => $this->request->route('accountId'),
                'error' => $e->getMessage(),
            ]);

            $errorCode = ErrorCode::INTERNAL_ERROR;
            return $this->response->json([
                'error' => $errorCode->getMessage(),
                'code' => $errorCode->value
            ])->withStatus($errorCode->getHttpStatus());
        }
    }

    #[GetMapping(path: '/account/{accountId}/balance')]
    public function getBalance(): \Psr\Http\Message\ResponseInterface
    {
        try {
            $accountId = $this->request->route('accountId');
            
            $account = $this->accountRepository->findById($accountId);
            
            if (!$account) {
                $errorCode = ErrorCode::ACCOUNT_NOT_FOUND;
                return $errorCode->getResponse($this->response);
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
                'account_id' => $this->request->route('accountId'),
                'error' => $e->getMessage(),
            ]);

            $errorCode = ErrorCode::INTERNAL_ERROR;
            return $this->response->json([
                'error' => $errorCode->getMessage(),
                'code' => $errorCode->value
            ])->withStatus($errorCode->getHttpStatus());
        }
    }
}
