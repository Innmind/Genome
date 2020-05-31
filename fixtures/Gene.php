<?php
declare(strict_types = 1);

namespace Fixtures\Innmind\Genome;

use Innmind\Genome\{
    Gene as Model,
    History,
};
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Server\Control\Server;
use Innmind\BlackBox\Set;

final class Gene
{
    /**
     * @return Set<Model>
     */
    public static function list(): Set
    {
        return Set\Sequence::of(
            Set\Decorate::immutable(
                static fn($name) => new class($name) implements Model {
                    private $name;

                    public function __construct($name)
                    {
                        $this->name = $name;
                    }

                    public function name(): string
                    {
                        return $this->name;
                    }

                    public function express(
                        OperatingSystem $os,
                        Server $target,
                        History $history
                    ): History {
                        return $history->add($this->name);
                    }
                },
                Set\Unicode::lengthBetween(1, 10),
            ),
            Set\Integers::between(1, 100),
        );
    }
}
