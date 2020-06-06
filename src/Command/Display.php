<?php
declare(strict_types = 1);

namespace Innmind\Genome\Command;

use Innmind\Genome\{
    Genome,
    Gene,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Url\Path;
use Innmind\Immutable\Str;

final class Display implements Command
{
    private OperatingSystem $os;

    public function __construct(OperatingSystem $os)
    {
        $this->os = $os;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        $path = $env->workingDirectory()->resolve(Path::of('genome.php'));

        if ($arguments->contains('genome')) {
            $path = Path::of($arguments->get('genome'));
        }

        if (!$this->os->filesystem()->contains($path)) {
            $env->error()->write(Str::of("{$path->toString()} doesn't exist\n"));
            $env->exit(1);

            return;
        }

        /**
         * @psalm-suppress UnresolvableInclude
         * @var mixed
         */
        $genome = require $path->toString();

        if (!$genome instanceof Genome) {
            $env->error()->write(Str::of("{$path->toString()} must return a Genome object\n"));
            $env->exit(1);

            return;
        }

        $genome->foreach(static function(Gene $gene) use ($env): void {
            $env->output()->write(Str::of("{$gene->name()}\n"));
        });
    }

    public function toString(): string
    {
        return <<<USAGE
        display [genome]

        Will list the genes that have been loaded

        When no `genome` path provided it will look
        for a genome.php in the working directory.
        USAGE;
    }
}
