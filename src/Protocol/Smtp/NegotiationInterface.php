<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Smtp;

interface NegotiationInterface
{
    /**
     * @param Client $client
     */
    public function negotiate(Client $client): void;
}
