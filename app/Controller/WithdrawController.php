<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\DTO\WithdrawRequestDTO;
use App\Enum\ErrorCode;
use App\Service\WithdrawService;
use App\Repository\AccountWithdrawRepository;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;
use Hyperf\Swagger\Annotation as SA;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Hyperf PIX Example API",
 *     version="1.0.0",
 *     description="API para saques PIX criado com HyperF 3"
 * )
 */
#[Controller]
class WithdrawController extends AbstractController
{
    #[Inject]
    protected WithdrawService $withdrawService;

    #[Inject]
    protected AccountWithdrawRepository $withdrawRepository;

    #[Inject]
    protected ValidatorFactoryInterface $validationFactory;

    private LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('withdraw-controller');
    }

    #[SA\Post(path: '/account/{accountId}/balance/withdraw', summary: 'Criar solicitação de saque', tags: ['Saque'])]
    #[PostMapping(path: '/account/{accountId}/balance/withdraw')]
    public function withdraw(RequestInterface $request, ResponseInterface $response): \Psr\Http\Message\ResponseInterface
    {
        try {
            $accountId = $request->route('accountId');
            $data = $request->all();

            $validator = $this->validationFactory->make($data, [
                'method' => 'required|string|in:PIX',
                'pix.type' => 'required|string|in:email',
                'pix.key' => 'required|email',
                'amount' => 'required|numeric|min:0.01',
                'schedule' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                $errorCode = ErrorCode::VALIDATION_ERROR;
                return $errorCode->getResponse($this->response, $validator->errors()->all());
            }

            if (!empty($data['schedule'])) {
                $scheduleDate = \Carbon\Carbon::parse($data['schedule'], \Hyperf\Config\config('app_timezone'));
                $maxDate = \Carbon\Carbon::now(\Hyperf\Config\config('app_timezone'))->addDays(7);
                
                if ($scheduleDate > $maxDate) {
                    $errorCode = ErrorCode::SCHEDULE_TOO_FAR;
                    return $this->response->json([
                        'error' => $errorCode->getMessage(),
                        'code' => $errorCode->value
                    ])->withStatus($errorCode->getHttpStatus());
                }
            }

            $withdrawRequest = WithdrawRequestDTO::fromArray($data);
            
            $result = $this->withdrawService->processWithdraw($accountId, $withdrawRequest);
            $resultArray = $result->toArray();

            return $this->response->json([
                'data' => $resultArray,
                'message' => $result->scheduled ? 'Withdraw scheduled successfully' : 'Withdraw processed successfully'
            ])->withStatus(201);

        } catch (\InvalidArgumentException $e) {
            $errorCode = ErrorCode::INVALID_REQUEST;
            return $this->response->json([
                'error' => $e->getMessage(),
                'code' => $errorCode->value
            ])->withStatus($errorCode->getHttpStatus());

        } catch (\Exception $e) {
            $this->logger->error('Error processing withdraw', [
                'account_id' => $request->route('accountId'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorCode = ErrorCode::INTERNAL_ERROR;
            return $this->response->json([
                'error' => $errorCode->getMessage(),
                'code' => $errorCode->value
            ])->withStatus($errorCode->getHttpStatus());
        }
    }

    #[SA\Get(path: '/account/{accountId}/withdraws', summary: 'Obter histórico de saques', tags: ['Saque'])]
    #[GetMapping(path: '/account/{accountId}/withdraws')]
    public function getWithdrawHistory(RequestInterface $request, ResponseInterface $response): \Psr\Http\Message\ResponseInterface
    {
        try {
            $accountId = $request->route('accountId');
            $limit = (int) ($request->query('limit', 50));
            $limit = min(max($limit, 1), 100);

            $history = $this->withdrawService->getWithdrawHistory($accountId, $limit);

            return $this->response->json([
                'data' => $history,
                'meta' => [
                    'total' => count($history),
                    'limit' => $limit,
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error getting withdraw history', [
                'account_id' => $request->route('accountId'),
                'error' => $e->getMessage(),
            ]);

            $errorCode = ErrorCode::INTERNAL_ERROR;
            return $this->response->json([
                'error' => $errorCode->getMessage(),
                'code' => $errorCode->value
            ])->withStatus($errorCode->getHttpStatus());
        }
    }

    #[SA\Get(path: '/account/{accountId}/withdraws/{withdrawId}', summary: 'Obter detalhes de um saque', tags: ['Saque'])]
    #[GetMapping(path: '/account/{accountId}/withdraws/{withdrawId}')]
    public function getWithdraw(RequestInterface $request, ResponseInterface $response): \Psr\Http\Message\ResponseInterface
    {
        try {
            $accountId = $request->route('accountId');
            $withdrawId = $request->route('withdrawId');

            $withdraw = $this->withdrawRepository->findById($withdrawId);

            if (!$withdraw || $withdraw->account_id !== $accountId) {
                $errorCode = ErrorCode::WITHDRAW_NOT_FOUND;
                return $errorCode->getResponse($this->response);
            }

            $result = \App\Domain\DTO\WithdrawResponseDTO::fromModel($withdraw);

            return $this->response->json([
                'data' => $result->toArray()
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Error getting withdraw', [
                'account_id' => $request->route('accountId'),
                'withdraw_id' => $request->route('withdrawId'),
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
