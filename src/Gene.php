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
    private $type;
    private $name;
    private $actions;
    private $update;

    private function __construct(
        Type $type,
        Name $name,
        StreamInterface $actions,
        StreamInterface $update
    ) {
        if ((string) $actions->type() !== 'string') {
            throw new \TypeError('Argument 3 must be of type StreamInterface<string>');
        }

        if ((string) $update->type() !== 'string') {
            throw new \TypeError('Argument 4 must be of type StreamInterface<string>');
        }

        $this->type = $type;
        $this->name = $name;
        $this->actions = $actions;
        $this->update = $update;
    }

    public static function template(
        Name $name,
        StreamInterface $actions = null,
        StreamInterface $update = null
    ): self {
        return new self(
            Type::template(),
            $name,
            $actions ?? Stream::of('string'),
            $update ?? Stream::of('string')
        );
    }

    public static function functional(
        Name $name,
        StreamInterface $actions = null,
        StreamInterface $update = null
    ): self {
        return new self(
            Type::functional(),
            $name,
            $actions ?? Stream::of('string'),
            $update ?? Stream::of('string')
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
    public function update(): StreamInterface
    {
        return $this->update;
    }
}
