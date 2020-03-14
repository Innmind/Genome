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
use Innmind\Filesystem\{
    Adapter,
    Name as FileName,
};
use Innmind\Json\Json;

final class Suppress implements Command
{
    private const FILE = 'expressed-genes.json';

    private Runner $suppress;
    private Adapter $filesystem;

    public function __construct(Runner $suppress, Adapter $filesystem)
    {
        $this->suppress = $suppress;
        $this->filesystem = $filesystem;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        if (!$this->filesystem->contains(new FileName(self::FILE))) {
            return;
        }

        /** @var list<array{gene: string, path: string}> */
        $expressed = Json::decode(
            $this
                ->filesystem
                ->get(new FileName(self::FILE))
                ->content()
                ->toString(),
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
                    Path::of($gene['path']),
                );
            }
        }
    }

    public function toString(): string
    {
        return <<<USAGE
suppress gene path

Will delete the expressed gene
USAGE;
    }
}
