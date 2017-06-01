<?php
declare(strict_types=1);

namespace Genkgo\Mail\Unit\Transport;

use Genkgo\Mail\AbstractTestCase;
use Genkgo\Mail\Address;
use Genkgo\Mail\AddressList;
use Genkgo\Mail\EmailAddress;
use Genkgo\Mail\GenericMessage;
use Genkgo\Mail\Header\From;
use Genkgo\Mail\Header\HeaderLine;
use Genkgo\Mail\Header\Subject;
use Genkgo\Mail\Header\To;
use Genkgo\Mail\HeaderInterface;
use Genkgo\Mail\Protocol\ConnectionInterface;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\ClientFactoryInterface;
use Genkgo\Mail\Stream\BitEncodedStream;
use Genkgo\Mail\Transport\EnvelopeFactory;
use Genkgo\Mail\Transport\SmtpTransport;

final class SmtpTransportTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function it_sends_messages()
    {
        $at = -1;
        $clientFactory = $this->createMock(ClientFactoryInterface::class);
        $connection = $this->createMock(ConnectionInterface::class);

        $message = (new GenericMessage())
            ->withHeader(new From(new Address(new EmailAddress('from@localhost'), 'name')))
            ->withHeader(new To(new AddressList([new Address(new EmailAddress('to@localhost'), 'name')])))
            ->withHeader(new Subject('subject'))
            ->withBody(new BitEncodedStream("test\r\ntest"));

        $clientFactory
            ->expects($this->once())
            ->method('newClient')
            ->willReturn(new Client($connection));

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("MAIL FROM:<from@localhost>")
            ->willReturn(1);

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("250 OK\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("RCPT TO:<to@localhost>")
            ->willReturn(1);

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("250 OK\r\n");

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("DATA\r\n")
            ->willReturn(1);

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("354 Send message content; end with CRLF\r\n");

        foreach ($message->getHeaders() as $headers) {
            /** @var HeaderInterface $header */
            foreach ($headers as $header) {
                $connection
                    ->expects($this->at(++$at))
                    ->method('send')
                    ->with(sprintf("%s\r\n", (string)(new HeaderLine($header))))
                    ->willReturn(1);
            }
        }

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with("\r\n")
            ->willReturn(1);

        foreach (explode("\r\n", (string)$message->getBody()) as $line) {
            $connection
                ->expects($this->at(++$at))
                ->method('send')
                ->with(sprintf("%s\r\n", $line))
                ->willReturn(1);
        }

        $connection
            ->expects($this->at(++$at))
            ->method('send')
            ->with(".\r\n")
            ->willReturn(1);

        $connection
            ->expects($this->at(++$at))
            ->method('receive')
            ->willReturn("250 OK\r\n");

        $transport = new SmtpTransport(
            $clientFactory,
            EnvelopeFactory::useExtractedHeader()
        );

        $transport->send($message);
    }
}