<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Server;

use Innmind\Genome\Server\Process;
use Innmind\Server\Control\Server\{
    Process as ProcessInterface,
    Process\Pid,
    Process\ExitCode,
    Process\Output,
    Process\Output\Type,
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class ProcessTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            ProcessInterface::class,
            new Process(
                $this->createMock(ProcessInterface::class),
                fn() => null,
            ),
        );
    }

    public function testPid()
    {
        $process = new Process(
            $inner = $this->createMock(ProcessInterface::class),
            fn() => null,
        );
        $inner
            ->expects($this->once())
            ->method('pid')
            ->willReturn($expected = new Pid(42));

        $this->assertSame($expected, $process->pid());
    }

    public function testOutput()
    {
        $process = new Process(
            $inner = $this->createMock(ProcessInterface::class),
            fn() => null,
        );

        $this->assertInstanceOf(Process\Output::class, $process->output());
    }

    public function testExitCode()
    {
        $process = new Process(
            $inner = $this->createMock(ProcessInterface::class),
            fn() => null,
        );
        $inner
            ->expects($this->once())
            ->method('exitCode')
            ->willReturn($expected = new ExitCode(42));

        $this->assertSame($expected, $process->exitCode());
    }

    public function testWait()
    {
        $called = false;
        $process = new Process(
            $inner = $this->createMock(ProcessInterface::class),
            function() use (&$called) {
                $called = true;
            },
        );
        $inner
            ->expects($this->once())
            ->method('output')
            ->willReturn($output = $this->createMock(Output::class));
        $output
            ->expects($this->once())
            ->method('foreach')
            ->with($this->callback(function($fn) {
                $fn(Str::of('foo'), Type::output());

                return true;
            }));
        $inner
            ->expects($this->once())
            ->method('wait');

        $this->assertNull($process->wait());
        $this->assertTrue($called);
    }

    public function testIsRunning()
    {
        $this
            ->forAll(Set\Elements::of(true, false))
            ->then(function($expected) {
                $process = new Process(
                    $inner = $this->createMock(ProcessInterface::class),
                    fn() => null,
                );
                $inner
                    ->expects($this->once())
                    ->method('isRunning')
                    ->willReturn($expected);

                $this->assertSame($expected, $process->isRunning());
            });
    }
}
