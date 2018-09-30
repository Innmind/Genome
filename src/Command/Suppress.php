<?php
declare(strict_types = 1);

namespace Innmind\Genome\Command;

use Innmind\Genome\{
    Suppress as Runner,
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

final class Suppress implements Command
{
    private const FILE = 'expressed-genes.json';

    private $suppress;
    private $filesystem;

    public function __construct(Runner $suppress, Adapter $filesystem)
    {
        $this->suppress = $suppress;
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

        $wanted = $arguments->get('gene');
        $path = $arguments->get('path');

        foreach ($expressed as $gene) {
            if (
                $gene['gene'] === $wanted &&
                $gene['path'] === $path
            ) {
                ($this->suppress)(
                    new Name($gene['gene']),
                    new Path($gene['path'])
                );
            }
        }
    }

    public function __toString(): string
    {
        return <<<USAGE
suppress gene path

Will delete the expressed gene
USAGE;
    }
}
