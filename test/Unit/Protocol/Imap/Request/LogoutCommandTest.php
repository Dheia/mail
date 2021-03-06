<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Imap\Request;

use Genkgo\Mail\Protocol\Imap\Request\LogoutCommand;
use Genkgo\Mail\Protocol\Imap\Tag;
use Genkgo\TestMail\AbstractTestCase;

final class LogoutCommandTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_creates_a_stream(): void
    {
        $command = new LogoutCommand(Tag::fromNonce(1));

        $this->assertSame('TAG1 LOGOUT', (string)$command->toStream());
        $this->assertSame('TAG1', (string)$command->getTag());
    }
}
