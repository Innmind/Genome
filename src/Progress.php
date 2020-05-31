<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Genome\Exception\{
    PreConditionFailed,
    ExpressionFailed,
};
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Server\Control\{
    Server as ServerInterface,
    Server\Process\Output\Type,
};
use Innmind\Immutable\Str;

final class Progress
{
    private OperatingSystem $os;
    private ServerInterface $target;
    /** @var list<Gene> */
    private array $genes;
    /** @var \Closure(Gene, History): void */
    private \Closure $onStart;
    /** @var \Closure(Gene, History): void */
    private \Closure $onExpressed;
    /** @var \Closure(PreConditionFailed, Gene, History): void */
    private \Closure $onPreConditionFailed;
    /** @var \Closure(ExpressionFailed, Gene, History): void */
    private \Closure $onExpressionFailed;
    /** @var \Closure(ServerInterface): ServerInterface */
    private \Closure $decorateServer;

    public function __construct(
        OperatingSystem $os,
        ServerInterface $target,
        Gene $gene,
        Gene ...$genes
    ) {
        $this->os = $os;
        $this->target = $target;
        $this->genes = [$gene, ...$genes];
        $this->onStart = static function(): void {};
        $this->onExpressed = static function(): void {};
        $this->onPreConditionFailed = static function(): void {};
        $this->onExpressionFailed = static function(): void {};
        $this->decorateServer = static fn(ServerInterface $server): ServerInterface => $server;
    }

    /**
     * @param callable(Gene, History): void $function
     */
    public function onStart(callable $function): self
    {
        $self = clone $this;
        $self->onStart = \Closure::fromCallable($function);

        return $self;
    }

    /**
     * @param callable(Gene, History): void $function
     */
    public function onExpressed(callable $function): self
    {
        $self = clone $this;
        $self->onExpressed = \Closure::fromCallable($function);

        return $self;
    }

    /**
     * @param callable(PreConditionFailed, Gene, History): void $function
     */
    public function onPreConditionFailed(callable $function): self
    {
        $self = clone $this;
        $self->onPreConditionFailed = \Closure::fromCallable($function);

        return $self;
    }

    /**
     * @param callable(ExpressionFailed, Gene, History): void $function
     */
    public function onExpressionFailed(callable $function): self
    {
        $self = clone $this;
        $self->onExpressionFailed = \Closure::fromCallable($function);

        return $self;
    }

    /**
     * @param callable(Str, Type): void $function
     */
    public function onOutput(callable $function): self
    {
        $decorateServer = $this->decorateServer;
        $self = clone $this;
        $self->decorateServer = static function(ServerInterface $server) use ($decorateServer, $function): ServerInterface {
            return new Server(
                $decorateServer($server),
                \Closure::fromCallable($function),
            );
        };

        return $self;
    }

    public function wait(): History
    {
        $history = new History;
        $target = ($this->decorateServer)($this->target);

        foreach ($this->genes as $gene) {
            try {
                ($this->onStart)($gene, $history);
                $history = $gene->express($this->os, $target, $history);
                ($this->onExpressed)($gene, $history);
            } catch (PreConditionFailed $e) {
                ($this->onPreConditionFailed)($e, $gene, $history);
                break;
            } catch (ExpressionFailed $e) {
                ($this->onExpressionFailed)($e, $gene, $history);
                break;
            }
        }

        return $history;
    }
}
