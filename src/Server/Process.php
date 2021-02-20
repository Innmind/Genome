<?php
declare(strict_types = 1);

namespace Innmind\Genome\Server;

use Innmind\Server\Control\Server\{
    Process as ProcessInterface,
    Process\Pid,
    Process\Output,
    Process\Output\Type,
    Process\ExitCode,
};
use Innmind\Immutable\Str;

final class Process implements ProcessInterface
{
    private ProcessInterface $process;
    /** @var \Closure(Str, Type): void */
    private \Closure $call;

    /**
     * @param \Closure(Str, Type): void $call
     */
    public function __construct(
        ProcessInterface $process,
        \Closure $call
    ) {
        $this->process = $process;
        $this->call = $call;
    }

    public function pid(): Pid
    {
        return $this->process->pid();
    }

    public function output(): Output
    {
        return new Process\Output(
            $this->process->output(),
            $this->call,
        );
    }

    public function exitCode(): ExitCode
    {
        return $this->process->exitCode();
    }

    public function wait(): void
    {
        $this->output()->foreach(static function() {});

        $this->process->wait();
    }

    public function isRunning(): bool
    {
        return $this->process->isRunning();
    }
}
