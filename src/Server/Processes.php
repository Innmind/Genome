<?php
declare(strict_types = 1);

namespace Innmind\Genome\Server;

use Innmind\Server\Control\Server\{
    Processes as ProcessesInterface,
    Process as ProcessInterface,
    Process\Pid,
    Process\Output\Type,
    Command,
    Signal,
};
use Innmind\Immutable\Str;

final class Processes implements ProcessesInterface
{
    private ProcessesInterface $processes;
    /** @var \Closure(Command): void */
    private \Closure $command;
    /** @var \Closure(Str, Type): void */
    private \Closure $output;

    /**
     * @param \Closure(Command): void $command
     * @param \Closure(Str, Type): void $output
     */
    public function __construct(
        ProcessesInterface $processes,
        \Closure $command,
        \Closure $output
    ) {
        $this->processes = $processes;
        $this->command = $command;
        $this->output = $output;
    }

    public function execute(Command $command): ProcessInterface
    {
        ($this->command)($command);

        return new Process(
            $this->processes->execute($command),
            $this->output,
        );
    }

    public function kill(Pid $pid, Signal $signal): void
    {
        $this->processes->kill($pid, $signal);
    }
}
