<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use Innmind\Genome\{
    Gene,
    Gene\Name,
    Gene\Type,
};
use Innmind\Immutable\StreamInterface;
use PHPUnit\Framework\TestCase;

class GeneTest extends TestCase
{
    public function testInterface()
    {
        $template = Gene::template(
            $name = new Name('innmind/library'),
            'foo',
            'bar'
        );

        $this->assertInstanceOf(Gene::class, $template);
        $this->assertSame(Type::template(), $template->type());
        $this->assertSame($name, $template->name());
        $this->assertInstanceOf(StreamInterface::class, $template->actions());
        $this->assertSame('string', (string) $template->actions()->type());
        $this->assertSame(['foo', 'bar'], $template->actions()->toPrimitive());

        $this->assertSame(Type::functional(), Gene::functional(new Name('a/b'))->type());
    }
}
