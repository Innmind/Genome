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
use Innmind\Url\Path;
use Innmind\Immutable\Sequence;
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

        $suppress(new Name('foo/bar'), Path::none());
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
                return $command->toString() === "rm '-r' '/working/directory'";
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait');
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(1));

        $this->expectException(GeneSuppressionFailed::class);
        $this->expectExceptionMessage('foo/bar');

        $suppress(new Name('foo/bar'), Path::of('/working/directory'));
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
                return $command->toString() === "composer 'global' 'remove' 'foo/bar'" &&
                    $command->workingDirectory()->toString() === '/working/directory';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait');
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(1));

        $this->expectException(GeneSuppressionFailed::class);
        $this->expectExceptionMessage('foo/bar');

        $suppress(new Name('foo/bar'), Path::of('/working/directory'));
    }

    public function testThrowWhenSuppressionFails()
    {
        $suppress = new Suppress(
            new Genome(
                Gene::functional(
                    new Name('foo/bar'),
                    Sequence::of('string'),
                    Sequence::of('string'),
                    Sequence::of('string', 'action1', 'action2')
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
                return $command->toString() === 'action1' &&
                    $command->workingDirectory()->toString() === '/working/directory';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait');
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(1));

        $this->expectException(GeneSuppressionFailed::class);
        $this->expectExceptionMessage('foo/bar');

        $suppress(new Name('foo/bar'), Path::of('/working/directory'));
    }

    public function testSuppress()
    {
        $suppress = new Suppress(
            new Genome(
                Gene::functional(
                    new Name('foo/bar'),
                    Sequence::of('string'),
                    Sequence::of('string'),
                    Sequence::of('string', 'action1', 'action2')
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
                return $command->toString() === 'action1' &&
                    $command->workingDirectory()->toString() === '/working/directory';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait');
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(0));
        $processes
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return $command->toString() === 'action2' &&
                    $command->workingDirectory()->toString() === '/working/directory';
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait');
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(0));
        $processes
            ->expects($this->at(2))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return $command->toString() === "composer 'global' 'remove' 'foo/bar'";
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait');
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(0));
        $processes
            ->expects($this->exactly(3))
            ->method('execute');

        $this->assertNull($suppress(new Name('foo/bar'), Path::of('/working/directory')));
    }
}
