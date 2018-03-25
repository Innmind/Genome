<?php
declare(strict_types = 1);

namespace Innmind\Genome\Loader;

use Innmind\Genome\{
    Loader,
    Genome,
    Gene,
    Gene\Name,
};
use Innmind\Url\PathInterface;
use Symfony\Component\Yaml\Yaml as Parser;

final class Yaml implements Loader
{
    public function __invoke(PathInterface $path): Genome
    {
        $config = Parser::parseFile((string) $path);
        $genes = [];

        foreach ($config['genome'] as $name => $gene) {
            $genes[] = Gene::{$gene['type']}(
                new Name($name),
                ...$gene['actions'] ?? []
            );
        }

        return new Genome(...$genes);
    }
}
