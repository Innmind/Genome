<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Command;

use Innmind\Genome\{
    Command\Mutate,
    Mutate as Runner,
    Genome,
    Gene,
    Gene\Name,
};
use Innmind\Server\Control\{
    Server,
    Server\Processes,
    Server\Process,
    Server\Process\ExitCode,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Filesystem\{
    Adapter,
    File\File,
    Stream\StringStream,
};
use Innmind\Immutable\Stream;
use PHPUnit\Framework\TestCase;

class MutateTest extends TestCase
{
    private $command;
    private $server;
    private $filesystem;

    public function setUp()
    {
        $this->command = new Mutate(
            new Runner(
                new Genome(
                    Gene::functional(
                        new Name('foo/bar'),
                        Stream::of('string'),
                        Stream::of('string')
                    )
                ),
                $this->server = $this->createMock(Server::class)
            ),
            $this->filesystem = $this->createMock(Adapter::class)
        );
    }

    public function testInterface()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testUsage()
    {
        $expected = <<<USAGE
mutate

Will update all the expressed genes
USAGE;

        $this->assertSame($expected, (string) $this->command);
    }

    public function testDoesNothingWhenNoFile()
    {
        $this
            ->filesystem
            ->expects($this->once())
            ->method('has')
            ->with('expressed-genes.json')
            ->willReturn(false);
        $this
            ->server
            ->expects($this->never())
            ->method('processes');

        $this->assertNull(($this->command)(
            $this->createMock(Environment::class),
            new Arguments,
            new Options
        ));
    }

    public function testMutateExpressedGenes()
    {
        $this
            ->filesystem
            ->expects($this->once())
            ->method('has')
            ->with('expressed-genes.json')
            ->willReturn(true);
        $this
            ->filesystem
            ->expects($this->once())
            ->method('get')
            ->with('expressed-genes.json')
            ->willReturn(new File(
                'expressed-genes.json',
                new StringStream('[{"gene":"foo/bar","path":"/somewhere"}]')
            ));
        $this
            ->server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
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

        $this->assertNull(($this->command)(
            $this->createMock(Environment::class),
            new Arguments,
            new Options
        ));
    }
}