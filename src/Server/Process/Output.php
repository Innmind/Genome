<?php
declare(strict_types = 1);

namespace Innmind\Genome\Server\Process;

use Innmind\Server\Control\Server\Process\{
    Output as OutputInterface,
    Output\Type,
};
use Innmind\Immutable\{
    Map,
    Str,
};

final class Output implements OutputInterface
{
    private OutputInterface $output;
    /** @var \Closure(Str, Type): void */
    private \Closure $call;

    /**
     * @param \Closure(Str, Type): void $call
     */
    public function __construct(
        OutputInterface $output,
        \Closure $call
    ) {
        $this->output = $output;
        $this->call = $call;
    }

    public function foreach(callable $function): void
    {
        $this->output->foreach(function(Str $chunk, Type $type) use ($function): void {
            ($this->call)($chunk, $type);
            $function($chunk, $type);
        });
    }

    public function reduce($carry, callable $reducer)
    {
        return $this->output->reduce($carry, $reducer);
    }

    public function filter(callable $predicate): OutputInterface
    {
        return $this->output->filter($predicate);
    }

    public function groupBy(callable $discriminator): Map
    {
        return $this->output->groupBy($discriminator);
    }

    public function partition(callable $predicate): Map
    {
        return $this->output->partition($predicate);
    }

    public function toString(): string
    {
        return $this->output->toString();
    }
}
