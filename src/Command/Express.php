<?php
declare(strict_types = 1);

namespace Innmind\Genome\Command;

use Innmind\Genome\{
    Genome,
    Gene,
    History\Event,
    Exception\PreConditionFailed,
    Exception\ExpressionFailed,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Server\Control\{
    Server,
    Server\Process\Output\Type
};
use Innmind\Url\{
    Url,
    Path,
};
use Innmind\Immutable\Str;

final class Express implements Command
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

        $remote = null;

        if ($options->contains('host')) {
            /** @var Url */
            $remote = Url::of($options->get('host'));
        }

        $genome
            ->express($this->os, $remote)
            ->onStart(static function(Gene $gene) use ($env): void {
                $env->output()->write(Str::of("Expressing {$gene->name()}...\n"));
            })
            ->onExpressed(static function(Gene $gene) use ($env): void {
                $env->output()->write(Str::of("{$gene->name()} expressed!\n"));
            })
            ->onPreConditionFailed(static function(PreConditionFailed $e) use ($env): void {
                $env->error()->write(Str::of("Pre condition failure: {$e->getMessage()}\n"));
            })
            ->onExpressionFailed(static function(ExpressionFailed $e) use ($env): void {
                $env->error()->write(Str::of("Expression failure: {$e->getMessage()}\n"));
            })
            ->onCommand(
                static function(Server\Command $command) use ($env): void {
                    $env->output()->write(Str::of("Executing: {$command->toString()}\n"));
                },
                static function(Str $chunk, Type $type) use ($env): void {
                    if ($type === Type::output()) {
                        $env->output()->write($chunk);
                    } else {
                        $env->error()->write($chunk);
                    }
                }
            )
            ->wait()
            ->foreach(static function(Event $event) use ($env): void {
                $env->output()->write(Str::of("Event: {$event->name()->toString()}..."));
            });
    }

    public function toString(): string
    {
        return <<<USAGE
        express [genome] -h|--host=

        Will express on the system a set of genes

        When no `genome` path provided it will look
        for a genome.php in the working directory.
        The --host option can contain an ssh url to
        the server on which to express the genes.
        USAGE;
    }
}
