<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use Innmind\Genome\{
    Gene,
    Gene\Name,
    Gene\Type,
};
use Innmind\Immutable\{
    StreamInterface,
    Stream,
};
use PHPUnit\Framework\TestCase;

class GeneTest extends TestCase
{
    public function testInterface()
    {
        $template = Gene::template(
            $name = new Name('innmind/library'),
            Stream::of('string', 'foo', 'bar'),
            Stream::of('string', 'baz', 'foobar')
        );

        $this->assertInstanceOf(Gene::class, $template);
        $this->assertSame(Type::template(), $template->type());
        $this->assertSame($name, $template->name());
        $this->assertInstanceOf(StreamInterface::class, $template->actions());
        $this->assertSame('string', (string) $template->actions()->type());
        $this->assertSame(['foo', 'bar'], $template->actions()->toPrimitive());
        $this->assertInstanceOf(StreamInterface::class, $template->update());
        $this->assertSame('string', (string) $template->update()->type());
        $this->assertSame(['baz', 'foobar'], $template->update()->toPrimitive());

        $this->assertSame(
            Type::functional(),
            Gene::functional(new Name('a/b'), Stream::of('string'), Stream::of('string'))->type()
        );
    }

    public function testThrowWhenInvalidStreamOfActions()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type StreamInterface<string>');

        Gene::template(new Name('a/b'), Stream::of('int'), Stream::of('string'));
    }

    public function testThrowWhenInvalidStreamOfUpdate()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 4 must be of type StreamInterface<string>');

        Gene::template(new Name('a/b'), Stream::of('string'), Stream::of('int'));
    }
}
