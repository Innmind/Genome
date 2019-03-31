<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use Innmind\Genome\{
    Genome,
    Gene,
    Gene\Name,
    Loader,
};
use Innmind\Url\PathInterface;
use Innmind\Immutable\{
    SetInterface,
    Stream,
};
use PHPUnit\Framework\TestCase;

class GenomeTest extends TestCase
{
    public function testInterface()
    {
        $genome = new Genome(
            $first = Gene::template(new Name('foo/bar'), Stream::of('string'), Stream::of('string')),
            $second = Gene::template(new Name('foo/baz'), Stream::of('string'), Stream::of('string'))
        );

        $this->assertTrue($genome->contains('foo/bar'));
        $this->assertTrue($genome->contains('foo/baz'));
        $this->assertFalse($genome->contains('foobar'));
        $this->assertSame($first, $genome->get('foo/bar'));
        $this->assertSame($second, $genome->get('foo/baz'));
        $this->assertInstanceOf(SetInterface::class, $genome->genes());
        $this->assertSame(Name::class, (string) $genome->genes()->type());
        $this->assertSame(
            [$first->name(), $second->name()],
            $genome->genes()->toPrimitive()
        );
    }

    public function testLoad()
    {
        $path = $this->createMock(PathInterface::class);
        $load = $this->createMock(Loader::class);
        $load
            ->expects($this->once())
            ->method('__invoke')
            ->with($path)
            ->willReturn($expected = new Genome);

        $genome = Genome::load($load, $path);

        $this->assertSame($expected, $genome);
    }

    public function testDefer()
    {
        $loaded = false;
        $genome = Genome::defer((function() use (&$loaded) {
            yield Gene::functional(
                new Gene\Name('innmind/installation-monitor')
            );

            try {
                yield Gene::functional(
                    new Gene\Name('innmind/foobar')
                );
            } finally {
                $loaded = true;
            }
        })());

        $this->assertInstanceOf(Genome::class, $genome);
        $this->assertFalse($loaded);
        $this->assertTrue($genome->contains('innmind/installation-monitor'));
        $this->assertTrue($loaded);
        $this->assertTrue($genome->contains('innmind/foobar'));
        $this->assertInstanceOf(Gene::class, $genome->get('innmind/installation-monitor'));
        $this->assertInstanceOf(Gene::class, $genome->get('innmind/foobar'));
    }
}
