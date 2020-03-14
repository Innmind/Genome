<?php
declare(strict_types = 1);

namespace Innmind\Genome\Command;

use Innmind\Genome\{
    Mutate as Runner,
    Gene\Name,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Url\Path;
use Innmind\Filesystem\Adapter;

final class Mutate implements Command
{
    private const FILE = 'expressed-genes.json';

    private Runner $mutate;
    private Adapter $filesystem;

    public function __construct(Runner $mutate, Adapter $filesystem)
    {
        $this->mutate = $mutate;
        $this->filesystem = $filesystem;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        if (!$this->filesystem->has(self::FILE)) {
            return;
        }

        $expressed = json_decode(
            (string) $this
                ->filesystem
                ->get(self::FILE)
                ->content(),
            true
        );

        foreach ($expressed as $gene) {
            ($this->mutate)(
                new Name($gene['gene']),
                new Path($gene['path'])
            );
        }
    }

    public function __toString(): string
    {
        return <<<USAGE
mutate

Will update all the expressed genes
USAGE;
    }
}
