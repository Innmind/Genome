<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\History\Event;

use Innmind\Genome\{
    History\Event\Name,
    Exception\DomainException,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class NameTest extends TestCase
{
    use BlackBox;

    public function testEmptyNameIsNotAccepted()
    {
        $this->expectException(DomainException::class);

        new Name('');
    }

    public function testAcceptAnyNonEmptyString()
    {
        $this
            ->forAll(Set\Unicode::lengthBetween(1, 128))
            ->then(function($value) {
                $this->assertSame($value, (new Name($value))->toString());
            });
    }

    public function testEquality()
    {
        $this
            ->forAll(Set\Unicode::lengthBetween(1, 128))
            ->then(function($name) {
                $a = new Name($name);
                $b = new Name($name);

                $this->assertTrue($a->equals($a));
                $this->assertTrue($a->equals($b));
            });
    }

    public function testInequality()
    {
        $this
            ->forAll(
                Set\Unicode::lengthBetween(1, 128),
                Set\Unicode::lengthBetween(1, 128),
            )
            ->filter(fn($a, $b) => $a !== $b)
            ->then(function($a, $b) {
                $a = new Name($a);
                $b = new Name($b);

                $this->assertFalse($a->equals($b));
                $this->assertFalse($b->equals($a));
            });
    }
}
