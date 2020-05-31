<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Command;

use Innmind\Genome\Command\Express;
use Innmind\OperatingSystem\{
    OperatingSystem,
    Factory,
    Filesystem,
    Remote,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Url\{
    Path,
    Url,
};
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

class ExpressTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Express($this->createMock(OperatingSystem::class)),
        );
    }

    public function testUsage()
    {
        $this->assertNotEmpty(
            (new Express($this->createMock(OperatingSystem::class)))->toString(),
        );
    }

    public function testUseLocalGenomeByDefault()
    {
        $express = new Express(
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
            ->expects($this->at(0))
            ->method('write')
            ->with(Str::of("Expressing hello...\n"));
        $output
            ->expects($this->at(1))
            ->method('write')
            ->with(Str::of("hello expressed!\n"));

        $this->assertNull($express(
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
                $express = new Express(
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
                    ->expects($this->at(0))
                    ->method('write')
                    ->with(Str::of("Expressing hello...\n"));
                $output
                    ->expects($this->at(1))
                    ->method('write')
                    ->with(Str::of("hello expressed!\n"));

                $this->assertNull($express(
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
        $express = new Express(
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

        $this->assertNull($express(
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
                $express = new Express(
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

                $this->assertNull($express(
                    $env,
                    new Arguments(
                        Map::of('string', 'string')
                            ('genome', __DIR__.'/../../fixtures/empty.php'),
                    ),
                    new Options,
                ));
            });
    }

    public function testExpressOnRemoteMachine()
    {
        $express = new Express(
            $os = $this->createMock(OperatingSystem::class),
        );
        $env = $this->createMock(Environment::class);
        $env
            ->expects($this->once())
            ->method('workingDirectory')
            ->willReturn(Path::of(__DIR__.'/../../fixtures/'));
        $env
            ->expects($this->never())
            ->method('exit');
        $env
            ->expects($this->any())
            ->method('output')
            ->willReturn($output = $this->createMock(Writable::class));
        $output
            ->expects($this->at(0))
            ->method('write')
            ->with(Str::of("Expressing hello...\n"));
        $output
            ->expects($this->at(1))
            ->method('write')
            ->with(Str::of("hello expressed!\n"));
        $os
            ->expects($this->once())
            ->method('filesystem')
            ->willReturn($filesystem = $this->createMock(Filesystem::class));
        $filesystem
            ->expects($this->once())
            ->method('contains')
            ->willReturn(true);
        $os
            ->expects($this->once())
            ->method('remote')
            ->willReturn($remote = $this->createMock(Remote::class));
        $remote
            ->expects($this->once())
            ->method('ssh')
            ->with(Url::of('ssh://example.com'));

        $this->assertNull($express(
            $env,
            new Arguments,
            new Options(
                Map::of('string', 'string')
                    ('host', 'ssh://example.com'),
            ),
        ));
    }
}
