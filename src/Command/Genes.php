<?php
declare(strict_types = 1);

namespace Innmind\Genome\Command;

use Innmind\Genome\{
    Genome,
    Gene\Name,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Immutable\Str;

final class Genes implements Command
{
    private Genome $genome;

    public function __construct(Genome $genome)
    {
        $this->genome = $genome;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        $this->genome->genes()->foreach(static function(Name $gene) use ($env): void {
            $env->output()->write(Str::of((string) $gene)->append("\n"));
        });
    }

    public function __toString(): string
    {
        return <<<USAGE
genes

List all the genes that can be expressed
USAGE;
    }
}
