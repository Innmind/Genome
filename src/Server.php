<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Server\Control\{
    Server as ServerInterface,
    Server\Processes,
    Server\Volumes,
    Server\Process\Output\Type,
};
use Innmind\Immutable\Str;

final class Server implements ServerInterface
{
    private ServerInterface $server;
    /** @var \Closure(Str, Type): void */
    private \Closure $call;

    /**
     * @param \Closure(Str, Type): void $call
     */
    public function __construct(ServerInterface $server, \Closure $call)
    {
        $this->server = $server;
        $this->call = $call;
    }

    public function processes(): Processes
    {
        return new Server\Processes(
            $this->server->processes(),
            $this->call,
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
