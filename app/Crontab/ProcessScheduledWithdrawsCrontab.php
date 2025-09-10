<?php

declare(strict_types=1);

namespace App\Crontab;

use Hyperf\Crontab\Annotation\Crontab;

#[Crontab(rule: "*/5 * * * *", name: "ProcessScheduledWithdraws", callback: "execute", memo: "Process scheduled withdraws every 5 minutes")]
class ProcessScheduledWithdrawsCrontab
{
    public function execute()
    {
        $container = \Hyperf\Context\ApplicationContext::getContainer();
        $command = $container->get(\App\Command\ProcessScheduledWithdrawsCommand::class);
        
        return $command->handle();
    }
}
