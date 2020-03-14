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
use Innmind\Filesystem\{
    Adapter,
    Name as FileName,
};
use Innmind\Json\Json;

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

        foreach ($expressed as $gene) {
            ($this->mutate)(
                new Name($gene['gene']),
                Path::of($gene['path']),
            );
        }
    }

    public function toString(): string
    {
        return <<<USAGE
mutate

Will update all the expressed genes
USAGE;
    }
}
