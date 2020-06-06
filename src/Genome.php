<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Url\Url;

final class Genome
{
    /** @var list<Gene> */
    private array $genes;

    public function __construct(Gene $gene, Gene ...$genes)
    {
        $this->genes = [$gene, ...$genes];
    }

    public function express(OperatingSystem $os, Url $remote = null): Progress
    {
        $target = $remote ? $os->remote()->ssh($remote) : $os->control();

        return new Progress($os, $target, ...$this->genes);
    }

    /**
     * @param callable(Gene): void $function
     */
    public function foreach(callable $function): void
    {
        foreach ($this->genes as $gene) {
            $function($gene);
        }
    }
}
