<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    Symfony\Component\Mailer\MailerInterface::class => function () {
        $transport = Symfony\Component\Mailer\Transport::fromDsn(
            sprintf(
                'smtp://%s:%s@%s:%s',
                env('MAIL_USERNAME', ''),
                env('MAIL_PASSWORD', ''),
                env('MAIL_HOST', 'localhost'),
                env('MAIL_PORT', 587)
            )
        );
        
        return new Symfony\Component\Mailer\Mailer($transport);
    },
];
