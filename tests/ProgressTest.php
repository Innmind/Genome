<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use Innmind\Genome\{
    Progress,
    Gene,
    History,
    Exception\PreConditionFailed,
    Exception\ExpressionFailed,
};
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Server\Control\Server;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Genome\Gene as Fixture;

class ProgressTest extends TestCase
{
    use BlackBox;

    public function testInstallAllGenes()
    {
        $os = $this->createMock(OperatingSystem::class);
        $target = $this->createMock(Server::class);
        $gene1 = $this->createMock(Gene::class);
        $gene1
            ->expects($this->once())
            ->method('express')
            ->will($this->returnArgument(2));
        $gene2 = $this->createMock(Gene::class);
        $gene2
            ->expects($this->once())
            ->method('express')
            ->will($this->returnArgument(2));
        $gene3 = $this->createMock(Gene::class);
        $gene3
            ->expects($this->once())
            ->method('express')
            ->willReturn($expected = new History);
        $progress = new Progress($os, $target, $gene1, $gene2, $gene3);

        $this->assertSame($expected, $progress->wait());
    }

    public function testAcceptAnyNumberOfGenes()
    {
        $this
            ->forAll(Fixture::list())
            ->then(function($genes) {
                $progress = new Progress(
                    $this->createMock(OperatingSystem::class),
                    $this->createMock(Server::class),
                    ...$genes,
                );

                $history = $progress->wait();
                $names = \array_map(
                    fn($gene) => $gene->name(),
                    $genes,
                );

                $this->assertFalse($history->empty());
                $history->foreach(function($event) use ($names) {
                    $this->assertContains($event->name()->toString(), $names);
                });
            });
    }

    public function testOnStart()
    {
        $this
            ->forAll(Fixture::list())
            ->then(function($genes) {
                $initial = new Progress(
                    $this->createMock(OperatingSystem::class),
                    $this->createMock(Server::class),
                    ...$genes,
                );
                $count = [];
                $progress = $initial->onStart(function($gene) use (&$count) {
                    $count[] = $gene->name();
                });

                $this->assertNotSame($initial, $progress);
                $this->assertInstanceOf(Progress::class, $progress);
                $initial->wait();
                $this->assertSame([], $count); // assert immutability of Progress
                $progress->wait();
                $this->assertSame(
                    \array_map(
                        fn($gene) => $gene->name(),
                        $genes,
                    ),
                    $count,
                );
            });
    }

    public function testOnExpressed()
    {
        $this
            ->forAll(Fixture::list())
            ->then(function($genes) {
                $initial = new Progress(
                    $this->createMock(OperatingSystem::class),
                    $this->createMock(Server::class),
                    ...$genes,
                );
                $count = [];
                $progress = $initial->onExpressed(function($gene) use (&$count) {
                    $count[] = $gene->name();
                });

                $this->assertNotSame($initial, $progress);
                $this->assertInstanceOf(Progress::class, $progress);
                $initial->wait();
                $this->assertSame([], $count); // assert immutability of Progress
                $progress->wait();
                $this->assertSame(
                    \array_map(
                        fn($gene) => $gene->name(),
                        $genes,
                    ),
                    $count,
                );
            });
    }

    public function testOnPreConditionFailed()
    {
        $gene1 = $this->createMock(Gene::class);
        $gene1
            ->expects($this->exactly(2))
            ->method('express')
            ->will($this->returnArgument(2));
        $gene2 = $this->createMock(Gene::class);
        $gene2
            ->expects($this->exactly(2))
            ->method('express')
            ->will($this->throwException(new PreConditionFailed));
        $gene3 = $this->createMock(Gene::class);
        $gene3
            ->expects($this->never())
            ->method('express');

        $initial = new Progress(
            $this->createMock(OperatingSystem::class),
            $this->createMock(Server::class),
            $gene1,
            $gene2,
            $gene3,
        );
        $count = [];
        $progress = $initial->onPreConditionFailed(function($exception, $gene) use (&$count) {
            $count[] = $gene->name();
        });

        $this->assertNotSame($initial, $progress);
        $this->assertInstanceOf(Progress::class, $progress);
        $initial->wait();
        $this->assertSame([], $count); // assert immutability of Progress
        $progress->wait();
        $this->assertCount(1, $count);
    }

    public function testOnExpressionFailed()
    {
        $gene1 = $this->createMock(Gene::class);
        $gene1
            ->expects($this->exactly(2))
            ->method('express')
            ->will($this->returnArgument(2));
        $gene2 = $this->createMock(Gene::class);
        $gene2
            ->expects($this->exactly(2))
            ->method('express')
            ->will($this->throwException(new ExpressionFailed));
        $gene3 = $this->createMock(Gene::class);
        $gene3
            ->expects($this->never())
            ->method('express');

        $initial = new Progress(
            $this->createMock(OperatingSystem::class),
            $this->createMock(Server::class),
            $gene1,
            $gene2,
            $gene3,
        );
        $count = [];
        $progress = $initial->onExpressionFailed(function($exception, $gene) use (&$count) {
            $count[] = $gene->name();
        });

        $this->assertNotSame($initial, $progress);
        $this->assertInstanceOf(Progress::class, $progress);
        $initial->wait();
        $this->assertSame([], $count); // assert immutability of Progress
        $progress->wait();
        $this->assertCount(1, $count);
    }
}
