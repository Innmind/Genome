<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use Innmind\Genome\{
    Gene,
    Gene\Name,
    Gene\Type,
};
use Innmind\Immutable\Sequence;
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class GeneTest extends TestCase
{
    public function testInterface()
    {
        $template = Gene::template(
            $name = new Name('innmind/library'),
            Sequence::of('string', 'foo', 'bar'),
            Sequence::of('string', 'baz', 'foobar'),
            Sequence::of('string', 'foobaz')
        );

        $this->assertInstanceOf(Gene::class, $template);
        $this->assertSame(Type::template(), $template->type());
        $this->assertSame($name, $template->name());
        $this->assertInstanceOf(Sequence::class, $template->actions());
        $this->assertSame('string', (string) $template->actions()->type());
        $this->assertSame(['foo', 'bar'], unwrap($template->actions()));
        $this->assertInstanceOf(Sequence::class, $template->mutations());
        $this->assertSame('string', (string) $template->mutations()->type());
        $this->assertSame(['baz', 'foobar'], unwrap($template->mutations()));
        $this->assertInstanceOf(Sequence::class, $template->suppressions());
        $this->assertSame('string', (string) $template->suppressions()->type());
        $this->assertSame(['foobaz'], unwrap($template->suppressions()));

        $this->assertSame(
            Type::functional(),
            Gene::functional(new Name('a/b'), Sequence::of('string'), Sequence::of('string'))->type()
        );
    }

    public function testThrowWhenInvalidStreamOfActions()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 3 must be of type Sequence<string>');

        Gene::template(new Name('a/b'), Sequence::of('int'), Sequence::of('string'));
    }

    public function testThrowWhenInvalidStreamOfMutations()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 4 must be of type Sequence<string>');

        Gene::template(new Name('a/b'), Sequence::of('string'), Sequence::of('int'));
    }

    public function testThrowWhenInvalidStreamOfSuppressions()
    {
        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage('Argument 5 must be of type Sequence<string>');

        Gene::template(
            new Name('a/b'),
            Sequence::of('string'),
            Sequence::of('string'),
            Sequence::of('int')
        );
    }
}
