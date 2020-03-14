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
    /** @var Map<string, Gene> */
    private Map $genes;
    /** @var \Generator<Gene>|null */
    private ?\Generator $generate = null;

    public function __construct(Gene ...$genes)
    {
        /** @var Map<string, Gene> */
        $this->genes = Sequence::of(Gene::class, ...$genes)->reduce(
            Map::of('string', Gene::class),
            static function(Map $genes, Gene $gene): Map {
                return $genes->put(
                    $gene->name()->toString(),
                    $gene
                );
            }
        );
    }

    public static function load(Loader $load, Path $path): self
    {
        return $load($path);
    }

    /**
     * @param \Generator<Gene> $generate
     */
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
        /** @var Set<Name> */
        return $this->all()->values()->toSetOf(
            Name::class,
            static fn(Gene $gene): \Generator => yield $gene->name(),
        );
    }

    /**
     * @return Map<string, Gene>
     */
    private function all(): Map
    {
        if (!$this->generate instanceof \Generator || !$this->generate->valid()) {
            return $this->genes;
        }

        foreach ($this->generate as $gene) {
            $this->genes = $this->genes->put(
                $gene->name()->toString(),
                $gene
            );
        }

        return $this->genes;
    }
}
