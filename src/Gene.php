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

    private function __construct(Type $type, Name $name, string ...$actions)
    {
        $this->type = $type;
        $this->name = $name;
        $this->actions = Stream::of('string', ...$actions);
    }

    public static function template(Name $name, string ...$actions): self
    {
        return new self(Type::template(), $name, ...$actions);
    }

    public static function functional(Name $name, string ...$actions): self
    {
        return new self(Type::functional(), $name, ...$actions);
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
}
