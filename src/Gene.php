<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Genome\Gene\{
    Type,
    Name,
};
use Innmind\Immutable\Sequence;
use function Innmind\Immutable\assertSequence;

final class Gene
{
    private Type $type;
    private Name $name;
    private Sequence $actions;
    private Sequence $mutations;
    private Sequence $suppressions;

    private function __construct(
        Type $type,
        Name $name,
        Sequence $actions,
        Sequence $mutations,
        Sequence $suppressions
    ) {
        assertSequence('string', $actions, 3);
        assertSequence('string', $mutations, 4);
        assertSequence('string', $suppressions, 5);

        $this->type = $type;
        $this->name = $name;
        $this->actions = $actions;
        $this->mutations = $mutations;
        $this->suppressions = $suppressions;
    }

    public static function template(
        Name $name,
        Sequence $actions = null,
        Sequence $mutations = null,
        Sequence $suppressions = null
    ): self {
        return new self(
            Type::template(),
            $name,
            $actions ?? Sequence::strings(),
            $mutations ?? Sequence::strings(),
            $suppressions ?? Sequence::strings()
        );
    }

    public static function functional(
        Name $name,
        Sequence $actions = null,
        Sequence $mutations = null,
        Sequence $suppressions = null
    ): self {
        return new self(
            Type::functional(),
            $name,
            $actions ?? Sequence::strings(),
            $mutations ?? Sequence::strings(),
            $suppressions ?? Sequence::strings()
        );
    }

    public function type(): Type
    {
        return $this->type;
    }

    public function name(): Name
    {
        return $this->name;
    }

    /**
     * @return Sequence<string>
     */
    public function actions(): Sequence
    {
        return $this->actions;
    }

    /**
     * @return Sequence<string>
     */
    public function mutations(): Sequence
    {
        return $this->mutations;
    }

    /**
     * @return Sequence<string>
     */
    public function suppressions(): Sequence
    {
        return $this->suppressions;
    }
}
