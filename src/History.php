<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Genome\History\Event;
use Innmind\Immutable\{
    Sequence,
    Map,
};

final class History
{
    /** @var Sequence<Event> */
    private Sequence $events;

    public function __construct()
    {
        /** @var Sequence<Event> */
        $this->events = Sequence::of(Event::class);
    }

    public function empty(): bool
    {
        return $this->events->empty();
    }

    /**
     * @param Map<string, mixed>|null $payload
     */
    public function add(string $name, Map $payload = null): self
    {
        /** @var Map<string, mixed> */
        $payload ??= Map::of('string', 'mixed');
        $self = clone $this;
        $self->events = ($this->events)(new Event(
            new Event\Name($name),
            $payload,
        ));

        return $self;
    }

    public function get(string $name): self
    {
        $self = clone $this;
        $self->events = $this
            ->events
            ->filter(static fn(Event $event): bool => $event->is($name));

        return $self;
    }

    /**
     * @param callable(Event): void $function
     */
    public function foreach(callable $function): void
    {
        $this->events->foreach($function);
    }
}
