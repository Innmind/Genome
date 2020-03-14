<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use Innmind\Genome\{
    Express,
    Genome,
    Gene,
    Gene\Name,
    Exception\UnknownGene,
    Exception\GeneExpressionFailed,
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

class ExpressTest extends TestCase
{
    public function testThrowWhenGeneNotFound()
    {
        $express = new Express(
            new Genome,
            $this->createMock(Server::class)
        );

        $this->expectException(UnknownGene::class);
        $this->expectExceptionMessage('foo/bar');

        $express(new Name('foo/bar'), Path::none());
    }

    public function testThrowWhenFailedToDeployTemplate()
    {
        $express = new Express(
            new Genome(
                Gene::template(new Name('foo/bar'), Sequence::of('string'), Sequence::of('string'))
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
                return $command->toString() === "composer 'create-project' 'foo/bar' '/working/directory' '--no-dev' '--prefer-source' '--keep-vcs'";
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait');
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(1));

        $this->expectException(GeneExpressionFailed::class);
        $this->expectExceptionMessage('foo/bar');

        $express(new Name('foo/bar'), Path::of('/working/directory'));
    }

    public function testThrowWhenFailedToDeployFunctionalGene()
    {
        $express = new Express(
            new Genome(
                Gene::functional(new Name('foo/bar'), Sequence::of('string'), Sequence::of('string'))
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
                return $command->toString() === "composer 'global' 'require' 'foo/bar'";
            }))
            ->willReturn($process = $this->createMock(Process::class));
        $process
            ->expects($this->once())
            ->method('wait');
        $process
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn(new ExitCode(1));

        $this->expectException(GeneExpressionFailed::class);
        $this->expectExceptionMessage('foo/bar');

        $express(new Name('foo/bar'), Path::of('/working/directory'));
    }

    public function testThrowWhenActionFails()
    {
        $express = new Express(
            new Genome(
                Gene::functional(
                    new Name('foo/bar'),
                    Sequence::of('string', 'action1', 'action2'),
                    Sequence::of('string')
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
                return $command->toString() === "composer 'global' 'require' 'foo/bar'";
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
        $processes
            ->expects($this->exactly(2))
            ->method('execute');

        $this->expectException(GeneExpressionFailed::class);
        $this->expectExceptionMessage('foo/bar');

        $express(new Name('foo/bar'), Path::of('/working/directory'));
    }

    public function testExpress()
    {
        $express = new Express(
            new Genome(
                Gene::functional(
                    new Name('foo/bar'),
                    Sequence::of('string', 'action1', 'action2'),
                    Sequence::of('string')
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
                return $command->toString() === "composer 'global' 'require' 'foo/bar'";
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
            ->expects($this->at(2))
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
            ->expects($this->exactly(3))
            ->method('execute');

        $this->assertNull($express(new Name('foo/bar'), Path::of('/working/directory')));
    }
}
