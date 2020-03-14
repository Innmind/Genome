<?php
declare(strict_types = 1);

namespace Innmind\Genome\Gene;

final class Type
{
    private static ?self $template = null;
    private static ?self $functional = null;

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function template(): self
    {
        return self::$template ??= new self('template');
    }

    public static function functional(): self
    {
        return self::$functional ??= new self('functional');
    }

    public function toString(): string
    {
        return $this->value;
    }
}
