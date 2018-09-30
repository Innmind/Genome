<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use Innmind\Genome\{
    Mutate,
    Genome,
    Gene,
    Gene\Name,
    Exception\UnknownGene,
    Exception\GeneMutationFailed,
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

class MutateTest extends TestCase
{
    public function testThrowWhenGeneNotFound()
    {
        $mutate = new Mutate(
            new Genome,
            $this->createMock(Server::class)
        );

        $this->expectException(UnknownGene::class);
        $this->expectExceptionMessage('foo/bar');

        $mutate(new Name('foo/bar'), $this->createMock(PathInterface::class));
    }

    public function testThrowWhenFailedToUpdateTemplate()
    {
        $mutate = new Mutate(
            new Genome(
                Gene::template(new Name('foo/bar'), Stream::of('string'), Stream::of('string'))
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
                return (string) $command === "composer 'update'" &&
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

        $this->expectException(GeneMutationFailed::class);
        $this->expectExceptionMessage('foo/bar');

        $mutate(new Name('foo/bar'), new Path('/working/directory'));
    }

    public function testThrowWhenFailedToMutateFunctionalGene()
    {
        $mutate = new Mutate(
            new Genome(
                Gene::functional(new Name('foo/bar'), Stream::of('string'), Stream::of('string'))
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
                return (string) $command === "composer 'global' 'update' 'foo/bar'" &&
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

        $this->expectException(GeneMutationFailed::class);
        $this->expectExceptionMessage('foo/bar');

        $mutate(new Name('foo/bar'), new Path('/working/directory'));
    }

    public function testThrowWhenUpdateFails()
    {
        $mutate = new Mutate(
            new Genome(
                Gene::functional(
                    new Name('foo/bar'),
                    Stream::of('string'),
                    Stream::of('string', 'action1', 'action2')
                )
            ),
            $server = $this->createMock(Server::class)
        );
        $server
            ->expects($this->exactly(2))
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === "composer 'global' 'update' 'foo/bar'" &&
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
        $processes
            ->expects($this->exactly(2))
            ->method('execute');

        $this->expectException(GeneMutationFailed::class);
        $this->expectExceptionMessage('foo/bar');

        $mutate(new Name('foo/bar'), new Path('/working/directory'));
    }

    public function testMutate()
    {
        $mutate = new Mutate(
            new Genome(
                Gene::functional(
                    new Name('foo/bar'),
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
                return (string) $command === "composer 'global' 'update' 'foo/bar'";
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
            ->expects($this->at(2))
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
            ->expects($this->exactly(3))
            ->method('execute');

        $this->assertNull($mutate(new Name('foo/bar'), new Path('/working/directory')));
    }
}
