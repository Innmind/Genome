<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Genome\Gene\Name;
use Innmind\Url\Path;
use Innmind\Immutable\{
    Sequence,
    Map,
    Set,
};

final class Genome
{
    private Map $genes;
    private ?\Generator $generate = null;

    public function __construct(Gene ...$genes)
    {
        $this->genes = Sequence::of(Gene::class, ...$genes)->reduce(
            Map::of('string', Gene::class),
            static function(Map $genes, Gene $gene): Map {
                return $genes->put(
                    (string) $gene->name(),
                    $gene
                );
            }
        );
    }

    public static function load(Loader $load, Path $path): self
    {
        return $load($path);
    }

    public static function defer(\Generator $generate): self
    {
        $self = new self;
        $self->generate = $generate;

        return $self;
    }

    public function get(string $name): Gene
    {
        return $this->all()->get($name);
    }

    public function contains(string $name): bool
    {
        return $this->all()->contains($name);
    }

    /**
     * @return Set<Name>
     */
    public function genes(): Set
    {
        return $this->all()->values()->reduce(
            Set::of(Name::class),
            static function(Set $names, Gene $gene): Set {
                return $names->add($gene->name());
            }
        );
    }

    private function all(): Map
    {
        if (!$this->generate instanceof \Generator || !$this->generate->valid()) {
            return $this->genes;
        }

        foreach ($this->generate as $gene) {
            $this->genes = $this->genes->put(
                (string) $gene->name(),
                $gene
            );
        }

        return $this->genes;
    }
}
