<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Loader;

use Innmind\Genome\{
    Loader\Packagist,
    Loader,
    Genome,
};
use Innmind\Url\Path;
use Innmind\HttpTransport\Transport;
use function Innmind\HttpTransport\bootstrap as http;
use PHPUnit\Framework\TestCase;

class PackagistTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Loader::class,
            new Packagist(
                $this->createMock(Transport::class)
            )
        );
    }

    public function testLoad()
    {
        $load = new Packagist(http()['default']());

        $genome = $load(new Path('/innmind'));

        $this->assertCount(14, $genome->genes());
        $this->assertTrue($genome->contains('innmind/library'));
        $this->assertTrue($genome->contains('innmind/crawler-app'));
        $this->assertTrue($genome->contains('innmind/profiler'));
        $this->assertTrue($genome->contains('innmind/installation-monitor'));
        $this->assertTrue($genome->contains('innmind/infrastructure-neo4j'));
        $this->assertTrue($genome->contains('innmind/infrastructure-amqp'));
        $this->assertTrue($genome->contains('innmind/infrastructure-nginx'));
        $this->assertTrue($genome->contains('innmind/tower'));
        $this->assertTrue($genome->contains('innmind/warden'));
        $this->assertTrue($genome->contains('innmind/git-release'));
        $this->assertTrue($genome->contains('innmind/lab-station'));
        $this->assertTrue($genome->contains('innmind/dependency-graph'));
        $this->assertTrue($genome->contains('innmind/genome'));
    }
}
