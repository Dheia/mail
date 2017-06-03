<?php
declare(strict_types=1);

namespace Genkgo\TestMail\Unit\Protocol\Smtp;

use Genkgo\Mail\Exception\ConnectionRefusedException;
use Genkgo\Mail\Protocol\Smtp\Client;
use Genkgo\Mail\Protocol\Smtp\ClientFactory;
use Genkgo\Mail\Protocol\Smtp\Request\NoopCommand;
use Genkgo\TestMail\AbstractTestCase;
use Genkgo\TestMail\Stub\FakeSmtpConnection;

final class ClientFactoryTest extends AbstractTestCase
{

    /**
     * @test
     */
    public function it_is_immutable()
    {
        $factory = new ClientFactory(new FakeSmtpConnection());

        $this->assertNotSame($factory, $factory->withAuthentication(Client::AUTH_AUTO, 'x', 'y'));
        $this->assertNotSame($factory, $factory->withEhlo('127.0.0.1'));
        $this->assertNotSame($factory, $factory->withTimeout(10));
        $this->assertNotSame($factory, $factory->withAllowInsecure());
    }

    /**
     * @test
     */
    public function it_throws_when_using_wrong_auth_method()
    {
        $this->expectException(\InvalidArgumentException::class);

        $factory = new ClientFactory(new FakeSmtpConnection());
        $factory->withAuthentication(99, 'x', 'y');
    }

    /**
     * @test
     */
    public function it_creates_connection_negotiator()
    {
        $connection = new FakeSmtpConnection();

        $factory = (new ClientFactory($connection))
            ->withEhlo('hostname');

        $client = $factory->newClient();
        $client->request(new NoopCommand());

        $this->assertArrayHasKey('crypto', $connection->getMetaData());
    }

    /**
     * @test
     */
    public function it_creates_connection_and_authentication_negotiator()
    {
        $connection = new FakeSmtpConnection();

        $factory = (new ClientFactory($connection))
            ->withEhlo('hostname')
            ->withAuthentication(Client::AUTH_PLAIN, 'user', 'pass');

        $client = $factory->newClient();
        $client->request(new NoopCommand());

        $this->assertArrayHasKey('crypto', $connection->getMetaData());
    }

    /**
     * @test
     */
    public function it_constructs_tcp_from_data_source_name()
    {
        $this->expectException(ConnectionRefusedException::class);
        $this->expectExceptionMessage('Could not create plain tcp connection. Connection refused.');

        $factory = ClientFactory::fromString(
            'smtp://user:pass@localhost/?ehlo=localhost&timeout=1&reconnectAfter=PT1S'
        );

        $factory
            ->newClient()
            ->request(new NoopCommand());
    }

    /**
     * @test
     */
    public function it_constructs_plain_tcp_from_data_source_name()
    {
        $this->expectException(ConnectionRefusedException::class);
        $this->expectExceptionMessage('Could not create plain tcp connection. Connection refused.');

        $factory = ClientFactory::fromString('smtp+plain://localhost/');

        $factory->newClient()->request(new NoopCommand());
    }

    /**
     * @test
     */
    public function it_constructs_tls_from_data_source_name()
    {
        $this->expectException(ConnectionRefusedException::class);
        $this->expectExceptionMessage('Could not create tls connection. Connection refused.');

        $factory = ClientFactory::fromString('smtp+tls://localhost/');

        $factory->newClient()->request(new NoopCommand());
    }

    /**
     * @test
     */
    public function it_constructs_ssl_from_data_source_name()
    {
        $this->expectException(ConnectionRefusedException::class);
        $this->expectExceptionMessage('Could not create ssl connection. Connection refused.');

        $factory = ClientFactory::fromString('smtp+ssl://localhost/');

        $factory->newClient()->request(new NoopCommand());
    }

    /**
     * @test
     */
    public function it_throws_when_incorrect_dsn()
    {
        $this->expectException(\InvalidArgumentException::class);

        ClientFactory::fromString('something');
    }

    /**
     * @test
     */
    public function it_throws_when_incorrect_protocol()
    {
        $this->expectException(\InvalidArgumentException::class);

        ClientFactory::fromString('xyz://host');
    }

}