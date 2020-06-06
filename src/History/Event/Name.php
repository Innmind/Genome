<?php
declare(strict_types = 1);

namespace Innmind\Genome\History\Event;

use Innmind\Genome\Exception\DomainException;
use Innmind\Immutable\Str;

final class Name
{
    private string $value;

    public function __construct(string $value)
    {
        if (Str::of($value)->empty()) {
            throw new DomainException;
        }

        $this->value = $value;
    }

    public function equals(self $name): bool
    {
        return $this->value === $name->toString();
    }

    public function toString(): string
    {
        return $this->value;
    }
}
