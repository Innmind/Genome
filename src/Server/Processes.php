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
    /** @var \Closure(Str, Type): void */
    private \Closure $call;

    /**
     * @param \Closure(Str, Type): void $call
     */
    public function __construct(
        ProcessesInterface $processes,
        \Closure $call
    ) {
        $this->processes = $processes;
        $this->call = $call;
    }

    public function execute(Command $command): ProcessInterface
    {
        return new Process(
            $this->processes->execute($command),
            $this->call,
        );
    }

    public function kill(Pid $pid, Signal $signal): void
    {
        $this->processes->kill($pid, $signal);
    }
}
