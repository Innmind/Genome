<?php
declare(strict_types = 1);

namespace Innmind\Genome\Gene;

final class Type
{
    private static $template;
    private static $functional;

    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function template(): self
    {
        return self::$template ?? self::$template = new self('template');
    }

    public static function functional(): self
    {
        return self::$functional ?? self::$functional = new self('functional');
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
