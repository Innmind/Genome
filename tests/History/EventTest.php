<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\History;

use Innmind\Genome\History\Event;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class EventTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this
            ->forAll(Set\Unicode::lengthBetween(1, 128))
            ->then(function($name) {
                $name = new Event\Name($name);
                $event = new Event(
                    $name,
                    $payload = Map::of('string', 'mixed'),
                );

                $this->assertSame($name, $event->name());
                $this->assertSame($payload, $event->payload());
            });
    }

    public function testIs()
    {
        $this
            ->forAll(Set\Unicode::lengthBetween(1, 128))
            ->then(function($name) {
                $event = new Event(
                    new Event\Name($name),
                    Map::of('string', 'mixed'),
                );

                $this->assertTrue($event->is($name));
            });
    }

    public function testIsNot()
    {
        $this
            ->forAll(
                Set\Unicode::lengthBetween(1, 128),
                Set\Unicode::lengthBetween(1, 128),
            )
            ->then(function($name, $other) {
                $event = new Event(
                    new Event\Name($name),
                    Map::of('string', 'mixed'),
                );

                $this->assertFalse($event->is($other));
            });
    }
}
