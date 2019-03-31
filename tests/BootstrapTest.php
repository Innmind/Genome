<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use function Innmind\Genome\bootstrap;
use Innmind\CLI\Commands;
use Innmind\Url\Path;
use Innmind\Filesystem\Adapter;
use Innmind\Server\Control\Server;
use Innmind\HttpTransport\Transport;
use PHPUnit\Framework\TestCase;

class BootstrapTest extends TestCase
{
    public function testBootstrap()
    {
        $this->assertInstanceOf(
            Commands::class,
            bootstrap(
                new Path(__DIR__.'/../genome.php'),
                $this->createMock(Adapter::class),
                $this->createMock(Server::class),
                $this->createMock(Transport::class)
            )
        );
    }
}
