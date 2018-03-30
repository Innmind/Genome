<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Url\PathInterface;
use Innmind\Immutable\{
    Sequence,
    MapInterface,
    Map,
};

final class Genome
{
    private $genes;

    public function __construct(Gene ...$genes)
    {
        $this->genes = Sequence::of(...$genes)->reduce(
            new Map('string', Gene::class),
            static function(MapInterface $genes, Gene $gene): MapInterface {
                return $genes->put(
                    (string) $gene->name(),
                    $gene
                );
            }
        );
    }

    public static function load(Loader $load, PathInterface $path): self
    {
        return $load($path);
    }

    public function get(string $name): Gene
    {
        return $this->genes->get($name);
    }

    public function contains(string $name): bool
    {
        return $this->genes->contains($name);
    }
}
