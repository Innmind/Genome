<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Loader;

use Innmind\Genome\{
    Loader\Packagist,
    Loader,
    Genome,
    Gene\Name,
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

        $genome = $load(Path::of('/innmind'));

        $this->assertCount(14, $genome->genes());
        $this->assertTrue($genome->contains(new Name('innmind/library')));
        $this->assertTrue($genome->contains(new Name('innmind/crawler-app')));
        $this->assertTrue($genome->contains(new Name('innmind/profiler')));
        $this->assertTrue($genome->contains(new Name('innmind/installation-monitor')));
        $this->assertTrue($genome->contains(new Name('innmind/infrastructure-neo4j')));
        $this->assertTrue($genome->contains(new Name('innmind/infrastructure-amqp')));
        $this->assertTrue($genome->contains(new Name('innmind/infrastructure-nginx')));
        $this->assertTrue($genome->contains(new Name('innmind/tower')));
        $this->assertTrue($genome->contains(new Name('innmind/warden')));
        $this->assertTrue($genome->contains(new Name('innmind/git-release')));
        $this->assertTrue($genome->contains(new Name('innmind/lab-station')));
        $this->assertTrue($genome->contains(new Name('innmind/dependency-graph')));
        $this->assertTrue($genome->contains(new Name('innmind/genome')));
    }
}
