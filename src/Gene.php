<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Genome\Gene\{
    Type,
    Name,
};
use Innmind\Immutable\{
    StreamInterface,
    Stream,
};

final class Gene
{
    private Type $type;
    private Name $name;
    private StreamInterface $actions;
    private StreamInterface $mutations;
    private StreamInterface $suppressions;

    private function __construct(
        Type $type,
        Name $name,
        StreamInterface $actions,
        StreamInterface $mutations,
        StreamInterface $suppressions
    ) {
        if ((string) $actions->type() !== 'string') {
            throw new \TypeError('Argument 3 must be of type StreamInterface<string>');
        }

        if ((string) $mutations->type() !== 'string') {
            throw new \TypeError('Argument 4 must be of type StreamInterface<string>');
        }

        if ((string) $suppressions->type() !== 'string') {
            throw new \TypeError('Argument 5 must be of type StreamInterface<string>');
        }

        $this->type = $type;
        $this->name = $name;
        $this->actions = $actions;
        $this->mutations = $mutations;
        $this->suppressions = $suppressions;
    }

    public static function template(
        Name $name,
        StreamInterface $actions = null,
        StreamInterface $mutations = null,
        StreamInterface $suppressions = null
    ): self {
        return new self(
            Type::template(),
            $name,
            $actions ?? Stream::of('string'),
            $mutations ?? Stream::of('string'),
            $suppressions ?? Stream::of('string')
        );
    }

    public static function functional(
        Name $name,
        StreamInterface $actions = null,
        StreamInterface $mutations = null,
        StreamInterface $suppressions = null
    ): self {
        return new self(
            Type::functional(),
            $name,
            $actions ?? Stream::of('string'),
            $mutations ?? Stream::of('string'),
            $suppressions ?? Stream::of('string')
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
     * @return StreamInterface<string>
     */
    public function actions(): StreamInterface
    {
        return $this->actions;
    }

    /**
     * @return StreamInterface<string>
     */
    public function mutations(): StreamInterface
    {
        return $this->mutations;
    }

    /**
     * @return StreamInterface<string>
     */
    public function suppressions(): StreamInterface
    {
        return $this->suppressions;
    }
}
