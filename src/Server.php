<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Server\Control\{
    Server as ServerInterface,
    Server\Processes,
    Server\Command,
    Server\Volumes,
    Server\Process\Output\Type,
};
use Innmind\Immutable\Str;

final class Server implements ServerInterface
{
    private ServerInterface $server;
    /** @var \Closure(Command): void */
    private \Closure $command;
    /** @var \Closure(Str, Type): void */
    private \Closure $output;

    /**
     * @param \Closure(Command): void $command
     * @param \Closure(Str, Type): void $output
     */
    public function __construct(
        ServerInterface $server,
        \Closure $command,
        \Closure $output
    ) {
        $this->server = $server;
        $this->command = $command;
        $this->output = $output;
    }

    public function processes(): Processes
    {
        return new Server\Processes(
            $this->server->processes(),
            $this->command,
            $this->output,
        );
    }

    public function volumes(): Volumes
    {
        return $this->server->volumes();
    }

    public function reboot(): void
    {
        $this->server->reboot();
    }

    public function shutdown(): void
    {
        $this->server->shutdown();
    }
}
