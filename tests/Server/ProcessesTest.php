<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Server;

use Innmind\Genome\Server\{
    Processes,
    Process,
};
use Innmind\Server\Control\Server\{
    Processes as ProcessesInterface,
    Process\Pid,
    Signal,
    Command,
};
use PHPUnit\Framework\TestCase;

class ProcessesTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            ProcessesInterface::class,
            new Processes(
                $this->createMock(ProcessesInterface::class),
                static fn() => null,
                static fn() => null,
            ),
        );
    }

    public function testExecute()
    {
        $expected = null;
        $processes = new Processes(
            $inner = $this->createMock(ProcessesInterface::class),
            static function($command) use (&$expected) {
                $expected = $command;
            },
            static fn() => null,
        );
        $command = Command::foreground('echo');
        $inner
            ->expects($this->once())
            ->method('execute')
            ->with($command);

        $this->assertInstanceOf(
            Process::class,
            $processes->execute($command),
        );
        $this->assertSame($expected, $command);
    }

    public function testKill()
    {
        $processes = new Processes(
            $inner = $this->createMock(ProcessesInterface::class),
            static fn() => null,
            static fn() => null,
        );
        $pid = new Pid(42);
        $signal = Signal::terminate();
        $inner
            ->expects($this->once())
            ->method('kill')
            ->with($pid, $signal);

        $this->assertNull($processes->kill($pid, $signal));
    }
}
