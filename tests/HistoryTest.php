<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use Innmind\Genome\History;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class HistoryTest extends TestCase
{
    use BlackBox;

    public function testIsEmptyBeDefault()
    {
        $history = new History;

        $this->assertTrue($history->empty());
    }

    public function testForeach()
    {
        $history = new History;

        $count = 0;
        $this->assertNull($history->foreach(function($event) use (&$count) {
            ++$count;
        }));
        $this->assertSame(0, $count);
    }

    public function testAdd()
    {
        $this
            ->forAll(Set\Unicode::lengthBetween(1, 128))
            ->then(function($name) {
                $initial = new History;
                $history = $initial->add(
                    $name,
                    $payload = Map::of('string', 'mixed'),
                );

                $this->assertNotSame($initial, $history);
                $this->assertInstanceOf(History::class, $history);
                $this->assertTrue($initial->empty());
                $this->assertFalse($history->empty());
                $count = 0;
                $this->assertNull($history->foreach(function($event) use (&$count, $name, $payload) {
                    $this->assertTrue($event->is($name));
                    $this->assertSame($payload, $event->payload());
                    ++$count;
                }));
                $this->assertSame(1, $count);
            });
    }

    public function testGet()
    {
        $this
            ->forAll(
                Set\Unicode::lengthBetween(1, 128),
                Set\Unicode::lengthBetween(1, 128),
                Set\Unicode::lengthBetween(1, 128),
            )
            ->then(function($a, $b, $c) {
                $history = (new History)
                    ->add($a)
                    ->add($b)
                    ->add($c);

                $historyA = $history->get($a);
                $historyB = $history->get($b);
                $historyC = $history->get($c);

                $this->assertFalse($historyA->empty());
                $this->assertFalse($historyB->empty());
                $this->assertFalse($historyC->empty());
                $this->assertFalse($historyA->get($a)->empty());
                $this->assertTrue($historyA->get($b)->empty());
                $this->assertTrue($historyA->get($c)->empty());
                $this->assertFalse($historyB->get($b)->empty());
                $this->assertTrue($historyB->get($a)->empty());
                $this->assertTrue($historyB->get($c)->empty());
                $this->assertFalse($historyC->get($c)->empty());
                $this->assertTrue($historyC->get($a)->empty());
                $this->assertTrue($historyC->get($b)->empty());
            });
    }

    public function testReduce()
    {
        $this
            ->forAll(Set\Sequence::of(Set\Unicode::lengthBetween(1, 128)))
            ->then(function($names) {
                $history = new History;

                foreach ($names as $name) {
                    $history = $history->add($name);
                }

                $this->assertSame(
                    $names,
                    $history->reduce(
                        [],
                        function($carry, $event) {
                            $carry[] = $event->name()->toString();

                            return $carry;
                        },
                    ),
                );
                $this->assertSame(
                    \implode('', $names),
                    $history->reduce(
                        '',
                        fn($string, $event) => $string.$event->name()->toString(),
                    ),
                );
            });
    }
}
