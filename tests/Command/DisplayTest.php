<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Command;

use Innmind\Genome\Command\Display;
use Innmind\OperatingSystem\{
    OperatingSystem,
    Factory,
    Filesystem,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Url\Path;
use Innmind\Stream\Writable;
use Innmind\Immutable\{
    Str,
    Map,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};
use Fixtures\Innmind\Url\Path as FPath;

class DisplayTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Display($this->createMock(OperatingSystem::class)),
        );
    }

    public function testUsage()
    {
        $this->assertNotEmpty(
            (new Display($this->createMock(OperatingSystem::class)))->toString(),
        );
    }

    public function testUseLocalGenomeByDefault()
    {
        $display = new Display(
            Factory::build(),
        );
        $env = $this->createMock(Environment::class);
        $env
            ->expects($this->once())
            ->method('workingDirectory')
            ->willReturn(Path::of(__DIR__.'/../../fixtures/'));
        $env
            ->expects($this->any())
            ->method('output')
            ->willReturn($output = $this->createMock(Writable::class));
        $output
            ->expects($this->once())
            ->method('write')
            ->with(Str::of("hello\n"));

        $this->assertNull($display(
            $env,
            new Arguments,
            new Options,
        ));
    }

    public function testUseSpecifiedGenome()
    {
        $this
            ->forAll(FPath::any())
            ->then(function($workingDirectory) {
                $display = new Display(
                    Factory::build(),
                );
                $env = $this->createMock(Environment::class);
                $env
                    ->expects($this->once())
                    ->method('workingDirectory')
                    ->willReturn($workingDirectory);
                $env
                    ->expects($this->any())
                    ->method('output')
                    ->willReturn($output = $this->createMock(Writable::class));
                $output
                    ->expects($this->once())
                    ->method('write')
                    ->with(Str::of("hello\n"));

                $this->assertNull($display(
                    $env,
                    new Arguments(
                        Map::of('string', 'string')
                            ('genome', __DIR__.'/../../fixtures/genome.php'),
                    ),
                    new Options,
                ));
            });
    }

    public function testFailWhenGenomePathDoesntExist()
    {
        $display = new Display(
            $os = $this->createMock(OperatingSystem::class),
        );
        $env = $this->createMock(Environment::class);
        $env
            ->expects($this->once())
            ->method('workingDirectory')
            ->willReturn(Path::of(__DIR__.'/../../fixtures/'));
        $env
            ->expects($this->once())
            ->method('exit')
            ->with(1);
        $env
            ->expects($this->never())
            ->method('output');
        $os
            ->expects($this->once())
            ->method('filesystem')
            ->willReturn($filesystem = $this->createMock(Filesystem::class));
        $filesystem
            ->expects($this->once())
            ->method('contains')
            ->with(Path::of(__DIR__.'/../../fixtures/genome.php'))
            ->willReturn(false);

        $this->assertNull($display(
            $env,
            new Arguments,
            new Options,
        ));
    }

    public function testFailWhenGenomeFileDoesntReturnAGenome()
    {
        $this
            ->forAll(FPath::any())
            ->then(function($workingDirectory) {
                $display = new Display(
                    Factory::build(),
                );
                $env = $this->createMock(Environment::class);
                $env
                    ->expects($this->once())
                    ->method('workingDirectory')
                    ->willReturn($workingDirectory);
                $env
                    ->expects($this->once())
                    ->method('exit')
                    ->with(1);
                $env
                    ->expects($this->never())
                    ->method('output');

                $this->assertNull($display(
                    $env,
                    new Arguments(
                        Map::of('string', 'string')
                            ('genome', __DIR__.'/../../fixtures/empty.php'),
                    ),
                    new Options,
                ));
            });
    }
}
