<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use Innmind\Genome\{
    Genome,
    Gene,
    Gene\Name,
    Loader,
};
use Innmind\Url\Path;
use Innmind\Immutable\{
    Set,
    Sequence,
};
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class GenomeTest extends TestCase
{
    public function testInterface()
    {
        $genome = new Genome(
            $first = Gene::template(new Name('foo/bar'), Sequence::of('string'), Sequence::of('string')),
            $second = Gene::template(new Name('foo/baz'), Sequence::of('string'), Sequence::of('string'))
        );

        $this->assertTrue($genome->contains(new Name('foo/bar')));
        $this->assertTrue($genome->contains(new Name('foo/baz')));
        $this->assertFalse($genome->contains(new Name('bar/foo')));
        $this->assertSame($first, $genome->get(new Name('foo/bar')));
        $this->assertSame($second, $genome->get(new Name('foo/baz')));
        $this->assertInstanceOf(Set::class, $genome->genes());
        $this->assertSame(Name::class, (string) $genome->genes()->type());
        $this->assertSame(
            [$first->name(), $second->name()],
            unwrap($genome->genes()),
        );
    }

    public function testLoad()
    {
        $path = Path::none();
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
        $this->assertTrue($genome->contains(new Name('innmind/installation-monitor')));
        $this->assertTrue($loaded);
        $this->assertTrue($genome->contains(new Name('innmind/foobar')));
        $this->assertInstanceOf(Gene::class, $genome->get(new Name('innmind/installation-monitor')));
        $this->assertInstanceOf(Gene::class, $genome->get(new Name('innmind/foobar')));
    }
}
