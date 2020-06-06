<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use Innmind\Genome\Server;
use Innmind\Server\Control\{
    Server as ServerInterface,
    Server\Volumes,
};
use PHPUnit\Framework\TestCase;

class RemoteServerTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ServerInterface::class,
            new Server(
                $this->createMock(ServerInterface::class),
                fn() => null,
                fn() => null,
            ),
        );
    }

    public function testProcesses()
    {
        $server = new Server(
            $this->createMock(ServerInterface::class),
            fn() => null,
            fn() => null,
        );

        $this->assertInstanceOf(Server\Processes::class, $server->processes());
    }

    public function testVolumes()
    {
        $server = new Server(
            $inner = $this->createMock(ServerInterface::class),
            fn() => null,
            fn() => null,
        );
        $inner
            ->expects($this->once())
            ->method('volumes');

        $this->assertInstanceOf(Volumes::class, $server->volumes());
    }

    public function testReboot()
    {
        $server = new Server(
            $inner = $this->createMock(ServerInterface::class),
            fn() => null,
            fn() => null,
        );
        $inner
            ->expects($this->once())
            ->method('reboot');

        $this->assertNull($server->reboot());
    }

    public function testShutdown()
    {
        $server = new Server(
            $inner = $this->createMock(ServerInterface::class),
            fn() => null,
            fn() => null,
        );
        $inner
            ->expects($this->once())
            ->method('shutdown');

        $this->assertNull($server->shutdown());
    }
}
