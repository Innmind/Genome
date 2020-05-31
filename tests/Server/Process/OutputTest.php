<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Server\Process;

use Innmind\Genome\Server\Process\Output;
use Innmind\Server\Control\Server\Process\{
    Output as OutputInterface,
    Output\Type,
};
use Innmind\Immutable\{
    Str,
    Map,
};
use PHPUnit\Framework\TestCase;
use Innmind\BlackBox\{
    PHPUnit\BlackBox,
    Set,
};

class OutputTest extends TestCase
{
    use BlackBox;

    public function testInterface()
    {
        $this->assertInstanceOf(
            OutputInterface::class,
            new Output(
                $this->createMock(OutputInterface::class),
                static fn() => null,
            ),
        );
    }

    public function testForeach()
    {
        $decorator = false;
        $standard = false;
        $expected = Str::of('foo');
        $output = new Output(
            $inner = $this->createMock(OutputInterface::class),
            function($chunk) use ($expected, &$decorator) {
                $this->assertSame($expected, $chunk);
                $decorator = true;
            },
        );
        $inner
            ->expects($this->once())
            ->method('foreach')
            ->with($this->callback(function($fn) use ($expected) {
                $fn($expected, Type::output());

                return true;
            }));

        $this->assertNull($output->foreach(function($chunk) use ($expected, &$standard) {
            $this->assertSame($expected, $chunk);
            $standard = true;
        }));
        $this->assertTrue($standard);
        $this->assertTrue($decorator);
    }

    public function testReduce()
    {
        $carry = new \stdClass;
        $reducer = function(){};
        $output = new Output(
            $inner = $this->createMock(OutputInterface::class),
            function() {},
        );
        $inner
            ->expects($this->once())
            ->method('reduce')
            ->with($carry, $reducer)
            ->willReturn($expected = new \stdClass);

        $this->assertSame($expected, $output->reduce($carry, $reducer));
    }

    public function testFilter()
    {
        $predicate = function(){};
        $output = new Output(
            $inner = $this->createMock(OutputInterface::class),
            function() {},
        );
        $inner
            ->expects($this->once())
            ->method('filter')
            ->with($predicate)
            ->willReturn($expected = $this->createMock(OutputInterface::class));

        $this->assertSame($expected, $output->filter($predicate));
    }

    public function testGroupBy()
    {
        $discriminator = function(){};
        $output = new Output(
            $inner = $this->createMock(OutputInterface::class),
            function() {},
        );
        $inner
            ->expects($this->once())
            ->method('groupBy')
            ->with($discriminator)
            ->willReturn($expected = Map::of('string', OutputInterface::class));

        $this->assertSame($expected, $output->groupBy($discriminator));
    }

    public function testPartition()
    {
        $predicate = function(){};
        $output = new Output(
            $inner = $this->createMock(OutputInterface::class),
            function() {},
        );
        $inner
            ->expects($this->once())
            ->method('partition')
            ->with($predicate)
            ->willReturn($expected = Map::of('string', OutputInterface::class));

        $this->assertSame($expected, $output->partition($predicate));
    }

    public function testToString()
    {
        $this
            ->forAll(Set\Unicode::strings())
            ->then(function($expected) {
                $output = new Output(
                    $inner = $this->createMock(OutputInterface::class),
                    function() {},
                );
                $inner
                    ->expects($this->once())
                    ->method('toString')
                    ->willReturn($expected);

                $this->assertSame($expected, $output->toString());
            });
    }
}
