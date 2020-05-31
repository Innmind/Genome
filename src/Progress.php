<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Genome\Exception\{
    PreConditionFailed,
    ExpressionFailed,
};
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Server\Control\Server;

final class Progress
{
    private OperatingSystem $os;
    private Server $target;
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

    public function __construct(
        OperatingSystem $os,
        Server $target,
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

    public function wait(): History
    {
        $history = new History;

        foreach ($this->genes as $gene) {
            try {
                ($this->onStart)($gene, $history);
                $history = $gene->express($this->os, $this->target, $history);
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
