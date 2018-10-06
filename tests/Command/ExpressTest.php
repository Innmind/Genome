<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Command;

use Innmind\Genome\{
    Command\Express,
    Express as Runner,
    Genome,
    Gene,
    Gene\Name,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Server\Control\{
    Server,
    Server\Processes,
    Server\Process,
    Server\Process\ExitCode,
};
use Innmind\Filesystem\{
    Adapter,
    File\File,
    Stream\StringStream,
};
use Innmind\Immutable\{
    Map,
    Stream,
};
use PHPUnit\Framework\TestCase;

class ExpressTest extends TestCase
{
    private $command;
    private $server;
    private $filesystem;

    public function setUp()
    {
        $this->command = new Express(
            new Runner(
                new Genome(
                    Gene::template(
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
express gene path

Express the given gene in the specified path

When expressing a functional gene the path will be used as the working
directory when calling the associated actions so the path must exist otherwise
it will fail
USAGE;

        $this->assertSame($expected, (string) $this->command);
    }

    public function testExecution()
    {
        $this
            ->server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === "composer 'create-project' 'foo/bar' '/somewhere' '--no-dev' '--prefer-source' '--keep-vcs'";
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
        $this
            ->filesystem
            ->expects($this->once())
            ->method('has')
            ->with('expressed-genes.json')
            ->willReturn(false);
        $this
            ->filesystem
            ->expects($this->once())
            ->method('add')
            ->with($this->callback(static function($file): bool {
                $expected = <<<JSON
[
    {
        "gene": "foo\/bar",
        "path": "\/somewhere"
    }
]
JSON;

                return (string) $file->name() === 'expressed-genes.json' &&
                    (string) $file->content() === $expected;
            }));

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

    public function testExecutionWhenAlreadyAGeneExpressed()
    {
        $this
            ->server
            ->expects($this->once())
            ->method('processes')
            ->willReturn($processes = $this->createMock(Processes::class));
        $processes
            ->expects($this->once())
            ->method('execute')
            ->with($this->callback(static function($command): bool {
                return (string) $command === "composer 'create-project' 'foo/bar' '/somewhere' '--no-dev' '--prefer-source' '--keep-vcs'";
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
                new StringStream('[{"gene":"innmind/warden","path":"/root"}]')
            ));
        $this
            ->filesystem
            ->expects($this->once())
            ->method('add')
            ->with($this->callback(static function($file): bool {
                $expected = <<<JSON
[
    {
        "gene": "innmind\/warden",
        "path": "\/root"
    },
    {
        "gene": "foo\/bar",
        "path": "\/somewhere"
    }
]
JSON;

                return (string) $file->name() === 'expressed-genes.json' &&
                    (string) $file->content() === $expected;
            }));

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
