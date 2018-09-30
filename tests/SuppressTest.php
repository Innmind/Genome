<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use Innmind\Genome\{
    Suppress,
    Genome,
    Gene,
    Gene\Name,
    Exception\UnknownGene,
    Exception\GeneSuppressionFailed,
};
use Innmind\Server\Control\{
    Server,
    Server\Processes,
    Server\Process,
    Server\Process\ExitCode,
};
use Innmind\Url\{
    PathInterface,
    Path,
};
use Innmind\Immutable\Stream;
use PHPUnit\Framework\TestCase;

class SuppressTest extends TestCase
{
    public function testThrowWhenGeneNotFound()
    {
        $suppress = new Suppress(
            new Genome,
            $this->createMock(Server::class)
        );

        $this->expectException(UnknownGene::class);
        $this->expectExceptionMessage('foo/bar');

        $suppress(new Name('foo/bar'), $this->createMock(PathInterface::class));
    }

    public function testThrowWhenFailedToSuppressTemplate()
    {
        $suppress = new Suppress(
            new Genome(
                Gene::template(new Name('foo/bar'))
            ),
            $server = $this->createMock(Server::class)
        );
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === "rm '-r' '/working/directory'";
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(1));

        $this->expectException(GeneSuppressionFailed::class);
        $this->expectExceptionMessage('foo/bar');

        $suppress(new Name('foo/bar'), new Path('/working/directory'));
    }

    public function testThrowWhenFailedToSuppressFunctionalGene()
    {
        $suppress = new Suppress(
            new Genome(
                Gene::functional(new Name('foo/bar'))
            ),
            $server = $this->createMock(Server::class)
        );
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === "composer 'global' 'remove' 'foo/bar'" &&
                    $command->workingDirectory() === '/working/directory';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(1));

        $this->expectException(GeneSuppressionFailed::class);
        $this->expectExceptionMessage('foo/bar');

        $suppress(new Name('foo/bar'), new Path('/working/directory'));
    }

    public function testThrowWhenSuppressionFails()
    {
        $suppress = new Suppress(
            new Genome(
                Gene::functional(
                    new Name('foo/bar'),
                    Stream::of('string'),
                    Stream::of('string'),
                    Stream::of('string', 'action1', 'action2')
                )
            ),
            $server = $this->createMock(Server::class)
        );
        $server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === 'action1' &&
                    $command->workingDirectory() === '/working/directory';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(1));

        $this->expectException(GeneSuppressionFailed::class);
        $this->expectExceptionMessage('foo/bar');

        $suppress(new Name('foo/bar'), new Path('/working/directory'));
    }

    public function testSuppress()
    {
        $suppress = new Suppress(
            new Genome(
                Gene::functional(
                    new Name('foo/bar'),
                    Stream::of('string'),
                    Stream::of('string'),
                    Stream::of('string', 'action1', 'action2')
                )
            ),
            $server = $this->createMock(Server::class)
        );
        $server
            ->expects($this->exactly(3))
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === 'action1' &&
                    $command->workingDirectory() === '/working/directory';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(0));
        $processes
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === 'action2' &&
                    $command->workingDirectory() === '/working/directory';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(0));
        $processes
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === "composer 'global' 'remove' 'foo/bar'";
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnSelf());
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(0));
        $processes
            ->expects($this->exactly(3))
            ->method('execute');

        $this->assertNull($suppress(new Name('foo/bar'), new Path('/working/directory')));
    }
}
