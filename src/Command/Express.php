<?php
declare(strict_types = 1);

namespace Innmind\Genome\Command;

use Innmind\Genome\{
    Express as Runner,
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
    File\File,
    Stream\StringStream,
};

final class Express implements Command
{
    private const FILE = 'expressed-genes.json';

    private Runner $express;
    private Adapter $filesystem;

    public function __construct(Runner $express, Adapter $filesystem)
    {
        $this->express = $express;
        $this->filesystem = $filesystem;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        ($this->express)(
            $gene = new Name($arguments->get('gene')),
            $path = new Path($arguments->get('path'))
        );

        $this->persist($gene, $path);
    }

    private function persist(Name $gene, Path $path): void
    {
        $expressed = [];

        if ($this->filesystem->has(self::FILE)) {
            $content = $this
                ->filesystem
                ->get(self::FILE)
                ->content();
            $expressed = json_decode((string) $content, true);
        }

        $expressed[] = [
            'gene' => (string) $gene,
            'path' => (string) $path,
        ];

        $this->filesystem->add(new File(
            self::FILE,
            new StringStream(json_encode($expressed, JSON_PRETTY_PRINT))
        ));
    }

    public function __toString(): string
    {
        return <<<USAGE
express gene path

Express the given gene in the specified path

When expressing a functional gene the path will be used as the working
directory when calling the associated actions so the path must exist otherwise
it will fail
USAGE;
    }
}
