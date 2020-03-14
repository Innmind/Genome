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
    Name as FileName,
};
use Innmind\Stream\Readable\Stream;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class SuppressTest extends TestCase
{
    private $command;
    private $server;
    private $filesystem;

    public function setUp(): void
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

        $this->assertSame($expected, $this->command->toString());
    }

    public function testDoesNothingWhenNoFile()
    {
        $this
            ->filesystem
            ->expects($this->once())
            ->method('contains')
            ->with(new FileName('expressed-genes.json'))
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
            ->method('contains')
            ->with(new FileName('expressed-genes.json'))
            ->willReturn(true);
        $this
            ->filesystem
            ->expects($this->once())
            ->method('get')
            ->with(new FileName('expressed-genes.json'))
            ->willReturn(File::named(
                'expressed-genes.json',
                Stream::ofContent('[{"gene":"foo/bar","path":"/somewhere"}]')
            ));
        $this
            ->server
            ->expects($this->never())
            ->method('processes');

        $this->assertNull(($this->command)(
            $this->createMock(Environment::class),
            new Arguments(
                Map::of('string', 'string')
                    ('gene', 'foo/bar')
                    ('path', '/somewhere-else')
            ),
            new Options
        ));
    }

    public function testSuppressGene()
    {
        $this
            ->filesystem
            ->expects($this->once())
            ->method('contains')
            ->with(new FileName('expressed-genes.json'))
            ->willReturn(true);
        $this
            ->filesystem
            ->expects($this->once())
            ->method('get')
            ->with(new FileName('expressed-genes.json'))
            ->willReturn(File::named(
                'expressed-genes.json',
                Stream::ofContent('[{"gene":"foo/bar","path":"/somewhere"}]')
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

        $this->assertNull(($this->command)(
            $this->createMock(Environment::class),
            new Arguments(
                Map::of('string', 'string')
                    ('gene', 'foo/bar')
                    ('path', '/somewhere')
            ),
            new Options
        ));
    }
}
