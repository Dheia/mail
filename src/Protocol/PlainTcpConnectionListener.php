<?php
declare(strict_types=1);

namespace Genkgo\Mail\Protocol;

use Genkgo\Mail\Exception\ConnectionListenerException;

/**
 * @codeCoverageIgnore
 */
final class PlainTcpConnectionListener implements ConnectionListenerInterface
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port;

    /**
     * @var resource
     */
    private $resource;

    /**
     * @var float
     */
    private $timeout;

    /**
     * @param string $host
     * @param int $port
     * @param float $timeout
     */
    public function __construct(string $host, int $port, float $timeout = -1.0)
    {
        $this->host = $host;
        $this->port = $port;
        $this->timeout = $timeout;
    }

    /**
     * @return ConnectionInterface
     * @throws ConnectionListenerException
     */
    public function listen(): ConnectionInterface
    {
        $this->validateResource();

        $resource = @\stream_socket_accept($this->resource, $this->timeout);
        if (\is_resource($resource)) {
            return new class($resource) extends AbstractConnection {
                /**
             * @param resource $resource
             */
                public function __construct($resource)
                {
                    $this->resource = $resource;
                }
                
                public function connect(): void
                {
                    $this->fireEvent('connect');
                }

                /**
             * @param int $type
             */
                public function upgrade(int $type): void
                {
                    if ($this->resource === null || \stream_socket_enable_crypto($this->resource, true, $type) === false) {
                        throw new \InvalidArgumentException('Cannot upgrade connection, resource not available');
                    }
                }
            };
        }

        throw new ConnectionListenerException('Could not accept connection');
    }
    
    private function validateResource(): void
    {
        if ($this->resource === null) {
            $resource = @\stream_socket_server(
                'tcp://' . $this->host . ':' . $this->port,
                $errorCode,
                $errorMessage
            );

            if ($resource === false) {
                throw new \UnexpectedValueException('Cannot create socket server');
            }

            $this->resource = $resource;
        }
    }
}
