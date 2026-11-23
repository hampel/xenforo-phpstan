<?php

namespace XF\Mail;

use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\Transport\TransportInterface;

class Mailer
{
    public function setDefaultTransport(AbstractTransport $transport): void
    {
    }

    /**
     * XenForo's docblock, which names the interface; every branch of the body returns an
     * AbstractTransport, and nothing on the mailer accepts less.
     *
     * @param string $type
     * @param array<string, mixed> $config
     *
     * @return TransportInterface
     */
    public static function getTransportFromOption($type, array $config)
    {
        return new class () extends AbstractTransport {
        };
    }
}
