<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Command;

use Innmind\Genome\{
    Command\Suppress,
    Suppress as Runner,
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
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class SuppressTest extends TestCase
{
    private $command;
    private $server;
    private $filesystem;

    public function setUp()
    {
        $this->command = new Suppress(
            new Runner(
                new Genome(
                    Gene::functional(new Name('foo/bar'))
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
suppress gene path

Will delete the expressed gene
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

    public function testDoesNothingWhenNoGeneExpressedAtLocation()
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
            ->expects($this->never())
            ->method('processes');

        $this->assertNull(($this->command)(
            $this->createMock(Environment::class),
            new Arguments(
                (new Map('string', 'mixed'))
                    ->put('gene', 'foo/bar')
                    ->put('path', '/somewhere-else')
            ),
            new Options
        ));
    }

    public function testSuppressGene()
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

        $this->assertNull(($this->command)(
            $this->createMock(Environment::class),
            new Arguments(
                (new Map('string', 'mixed'))
                    ->put('gene', 'foo/bar')
                    ->put('path', '/somewhere')
            ),
            new Options
        ));
    }
}
