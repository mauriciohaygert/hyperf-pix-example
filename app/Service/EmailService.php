<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\AccountWithdraw;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;
use Hyperf\Config\Annotation\Value;

class EmailService
{
    private LoggerInterface $logger;

    public function __construct(
        private MailerInterface $mailer,
        LoggerFactory $loggerFactory,
        #[Value('mail.from.address')] private string $fromAddress = 'noreply@hyperf-pix-example.com',
        #[Value('mail.from.name')] private string $fromName = 'Hyperf PIX Example'
    ) {
        $this->logger = $loggerFactory->get('email');
    }

    public function sendWithdrawNotification(AccountWithdraw $withdraw, bool $success = true, ?string $errorReason = null): bool
    {
        try {
            if (!$withdraw->pix || !$withdraw->pix->isValidEmail()) {
                $this->logger->error('Invalid PIX email for notification', [
                    'withdraw_id' => $withdraw->id,
                    'pix_key' => $withdraw->pix?->key ?? 'null',
                ]);
                return false;
            }

            $subject = $success ? 'Confirmação de Saque PIX - Hyperf PIX Example' : 'Saque PIX Negado';
            
            $email = (new Email())
                ->from($this->fromAddress)
                ->to($withdraw->pix->key)
                ->subject($subject)
                ->html($this->buildEmailContent($withdraw, $success, $errorReason));

            $this->mailer->send($email);

            $this->logger->info('Withdraw notification sent successfully', [
                'withdraw_id' => $withdraw->id,
                'recipient' => $withdraw->pix->key,
                'amount' => $withdraw->amount,
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Failed to send withdraw notification', [
                'withdraw_id' => $withdraw->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function sendScheduledWithdrawNotification(AccountWithdraw $withdraw): bool
    {
        try {
            if (!$withdraw->pix || !$withdraw->pix->isValidEmail()) {
                $this->logger->error('Invalid PIX email for scheduled notification', [
                    'withdraw_id' => $withdraw->id,
                    'pix_key' => $withdraw->pix?->key ?? 'null',
                ]);
                return false;
            }

            $subject = 'Saque PIX Agendado';
            
            $email = (new Email())
                ->from($this->fromAddress)
                ->to($withdraw->pix->key)
                ->subject($subject)
                ->html($this->buildScheduledEmailContent($withdraw));

            $this->mailer->send($email);

            $this->logger->info('Scheduled withdraw notification sent successfully', [
                'withdraw_id' => $withdraw->id,
                'recipient' => $withdraw->pix->key,
                'amount' => $withdraw->amount,
                'scheduled_for' => $withdraw->scheduled_for,
            ]);

            return true;

        } catch (\Exception $e) {
            $this->logger->error('Failed to send scheduled withdraw notification', [
                'withdraw_id' => $withdraw->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function buildEmailContent(AccountWithdraw $withdraw, bool $success = true, ?string $errorReason = null): string
    {
        $amount = 'R$ ' . number_format((float) $withdraw->amount, 2, ',', '.');
        $date = $withdraw->created_at->setTimezone(new \DateTimeZone(\Hyperf\Config\config('app_timezone')))->format('d/m/Y H:i:s');
        $pixKey = $withdraw->pix->key;
        $pixType = ucfirst($withdraw->pix->type);

        if ($success) {
            return $this->buildSuccessEmailContent($withdraw, $amount, $date, $pixKey, $pixType);
        }
        return $this->buildErrorEmailContent($withdraw, $amount, $date, $pixKey, $pixType, $errorReason);
    }

    private function buildSuccessEmailContent(AccountWithdraw $withdraw, string $amount, string $date, string $pixKey, string $pixType): string
    {
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Confirmação de Saque PIX</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .info-box { background-color: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>✅ Saque PIX Realizado</h1>
                </div>
                <div class='content'>
                    <p>Olá,</p>
                    <p>Seu saque PIX foi processado com sucesso!</p>
                    
                    <div class='info-box'>
                        <h3>Detalhes do Saque:</h3>
                        <p><strong>Data e Hora:</strong> {$date}</p>
                        <p><strong>Valor Sacado:</strong> {$amount}</p>
                        <p><strong>Chave PIX ({$pixType}):</strong> {$pixKey}</p>
                        <p><strong>ID da Transação:</strong> {$withdraw->id}</p>
                    </div>
                    
                    <p>O valor será creditado em sua conta vinculada à chave PIX informada.</p>
                    <p>Em caso de dúvidas, entre em contato conosco.</p>
                </div>
                <div class='footer'>
                    <p>Este é um e-mail automático. Não responda esta mensagem.</p>
                    <p>&copy; 2024 Hyperf PIX - Todos os direitos reservados</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private function buildErrorEmailContent(AccountWithdraw $withdraw, string $amount, string $date, string $pixKey, string $pixType, ?string $errorReason): string
    {
        $reasonText = $this->getErrorReasonText($errorReason);
        
        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Saque PIX Negado</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .info-box { background-color: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .error-box { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>❌ Saque PIX Negado</h1>
                </div>
                <div class='content'>
                    <p>Olá,</p>
                    <p>Infelizmente, seu saque PIX não pôde ser processado.</p>
                    
                    <div class='error-box'>
                        <h3>Motivo da Negação:</h3>
                        <p><strong>{$reasonText}</strong></p>
                    </div>
                    
                    <div class='info-box'>
                        <h3>Detalhes da Tentativa:</h3>
                        <p><strong>Data e Hora:</strong> {$date}</p>
                        <p><strong>Valor Solicitado:</strong> {$amount}</p>
                        <p><strong>Chave PIX ({$pixType}):</strong> {$pixKey}</p>
                        <p><strong>ID da Transação:</strong> {$withdraw->id}</p>
                    </div>
                    
                    <p>Para resolver esta situação, verifique seu saldo disponível e tente novamente.</p>
                    <p>Em caso de dúvidas, entre em contato conosco.</p>
                </div>
                <div class='footer'>
                    <p>Este é um e-mail automático. Não responda esta mensagem.</p>
                    <p>&copy; 2024 Hyperf PIX - Todos os direitos reservados</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private function buildScheduledEmailContent(AccountWithdraw $withdraw): string
    {
        $amount = 'R$ ' . number_format((float) $withdraw->amount, 2, ',', '.');
        $scheduledDate = $withdraw->scheduled_for->setTimezone(new \DateTimeZone(\Hyperf\Config\config('app_timezone')))->format('d/m/Y H:i:s');
        $createdDate = $withdraw->created_at->setTimezone(new \DateTimeZone(\Hyperf\Config\config('app_timezone')))->format('d/m/Y H:i:s');
        $pixKey = $withdraw->pix->key;
        $pixType = ucfirst($withdraw->pix->type);

        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Saque PIX Agendado</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background-color: #f8f9fa; }
                .info-box { background-color: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .scheduled-box { background-color: #e3f2fd; border: 1px solid #2196f3; color: #1976d2; padding: 15px; margin: 10px 0; border-radius: 5px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📅 Saque PIX Agendado</h1>
                </div>
                <div class='content'>
                    <p>Olá,</p>
                    <p>Seu saque PIX foi agendado com sucesso!</p>
                    
                    <div class='scheduled-box'>
                        <h3>⏰ Agendamento Confirmado:</h3>
                        <p><strong>Data e Hora Programada:</strong> {$scheduledDate}</p>
                    </div>
                    
                    <div class='info-box'>
                        <h3>Detalhes do Saque:</h3>
                        <p><strong>Data de Solicitação:</strong> {$createdDate}</p>
                        <p><strong>Valor a Sacar:</strong> {$amount}</p>
                        <p><strong>Chave PIX ({$pixType}):</strong> {$pixKey}</p>
                        <p><strong>ID da Transação:</strong> {$withdraw->id}</p>
                    </div>
                    
                    <p>O saque será processado automaticamente na data e hora agendada.</p>
                    <p>Você receberá uma nova notificação quando o saque for executado.</p>
                    <p>Em caso de dúvidas, entre em contato conosco.</p>
                </div>
                <div class='footer'>
                    <p>Este é um e-mail automático. Não responda esta mensagem.</p>
                    <p>&copy; 2024 Hyperf PIX - Todos os direitos reservados</p>
                </div>
            </div>
        </body>
        </html>
        ";
    }

    private function getErrorReasonText(?string $errorReason): string
    {
        return match ($errorReason) {
            'Insufficient balance' => 'Saldo insuficiente para realizar o saque',
            'Account not found' => 'Conta não encontrada',
            'Invalid PIX key' => 'Chave PIX inválida',
            default => $errorReason ?? 'Erro não especificado'
        };
    }
}
