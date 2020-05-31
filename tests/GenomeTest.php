<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use Innmind\Genome\{
    Genome,
    Progress,
};
use Innmind\OperatingSystem\{
    OperatingSystem,
    Remote,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Genome\Gene;
use Fixtures\Innmind\Url\Url;

class GenomeTest extends TestCase
{
    use BlackBox;

    public function testLocalExpression()
    {
        $this
            ->forAll(Gene::list())
            ->then(function($genes) {
                $genome = new Genome(...$genes);
                $os = $this->createMock(OperatingSystem::class);
                $os
                    ->expects($this->once())
                    ->method('control');
                $os
                    ->expects($this->never())
                    ->method('remote');

                $this->assertInstanceOf(Progress::class, $genome->express($os));
            });
    }

    public function testRemoteExpression()
    {
        $this
            ->forAll(
                Gene::list(),
                Url::any(),
            )
            ->then(function($genes, $url) {
                $genome = new Genome(...$genes);
                $os = $this->createMock(OperatingSystem::class);
                $os
                    ->expects($this->never())
                    ->method('control');
                $os
                    ->expects($this->once())
                    ->method('remote')
                    ->willReturn($remote = $this->createMock(Remote::class));
                $remote
                    ->expects($this->once())
                    ->method('ssh')
                    ->with($url);

                $this->assertInstanceOf(Progress::class, $genome->express($os, $url));
            });
    }
}
