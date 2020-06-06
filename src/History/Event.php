<?php
declare(strict_types = 1);

namespace Innmind\Genome\History;

use Innmind\Genome\History\Event\Name;
use Innmind\Immutable\Map;
use function Innmind\Immutable\assertMap;

final class Event
{
    private Name $name;
    /** @var Map<string, mixed> */
    private Map $payload;

    /**
     * @param Map<string, mixed> $payload
     */
    public function __construct(Name $name, Map $payload)
    {
        assertMap('string', 'mixed', $payload, 2);

        $this->name = $name;
        $this->payload = $payload;
    }

    public function name(): Name
    {
        return $this->name;
    }

    /**
     * @return Map<string, mixed>
     */
    public function payload(): Map
    {
        return $this->payload;
    }

    public function is(string $name): bool
    {
        return $this->name->equals(new Name($name));
    }
}
