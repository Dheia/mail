<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol\Imap\Response;

use Genkgo\Mail\Exception\AssertionFailedException;
use Genkgo\Mail\Protocol\Imap\ResponseInterface;

final class CommandContinuationRequestResponse implements ResponseInterface
{
    /**
     * @var string
     */
    private $line;

    /**
     * @param string $line
     */
    public function __construct(string $line)
    {
        $this->line = $line;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return \sprintf('+ %s', $this->line);
    }

    /**
     * @param CompletionResult $expectedResult
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertCompletion(CompletionResult $expectedResult): ResponseInterface
    {
        throw new AssertionFailedException();
    }

    /**
     * @return ResponseInterface
     */
    public function assertContinuation(): ResponseInterface
    {
        return $this;
    }

    /**
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertTagged(): ResponseInterface
    {
        throw new AssertionFailedException('A command continuous request is never tagged');
    }

    /**
     * @param string $className
     * @return ResponseInterface
     * @throws AssertionFailedException
     */
    public function assertParsed(string $className): ResponseInterface
    {
        throw new AssertionFailedException('A command continuous response is never parsed');
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->line;
    }
}
