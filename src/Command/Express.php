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
    Name as FileName,
};
use Innmind\Stream\Readable\Stream;
use Innmind\Json\Json;

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
            $path = Path::of($arguments->get('path'))
        );

        $this->persist($gene, $path);
    }

    private function persist(Name $gene, Path $path): void
    {
        /** @var list<array{gene: string, path: string}> */
        $expressed = [];

        if ($this->filesystem->contains(new FileName(self::FILE))) {
            $content = $this
                ->filesystem
                ->get(new FileName(self::FILE))
                ->content();
            /** @var list<array{gene: string, path: string}> */
            $expressed = Json::decode($content->toString());
        }

        $expressed[] = [
            'gene' => $gene->toString(),
            'path' => $path->toString(),
        ];

        $this->filesystem->add(File::named(
            self::FILE,
            Stream::ofContent(Json::encode($expressed, \JSON_PRETTY_PRINT))
        ));
    }

    public function toString(): string
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
