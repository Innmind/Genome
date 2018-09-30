<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Genome\Gene\Name;
use Innmind\Url\PathInterface;
use Innmind\Immutable\{
    Sequence,
    MapInterface,
    Map,
    SetInterface,
    Set,
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

    /**
     * @return SetInterface<Name>
     */
    public function genes(): SetInterface
    {
        return $this->genes->values()->reduce(
            Set::of(Name::class),
            static function(SetInterface $names, Gene $gene): SetInterface {
                return $names->add($gene->name());
            }
        );
    }
}
