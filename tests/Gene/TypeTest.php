<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Gene;

use Innmind\Genome\Gene\Type;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    public function testInterface()
    {
        $template = Type::template();

        $this->assertInstanceOf(Type::class, $template);
        $this->assertSame('template', (string) $template);
        $this->assertSame($template, Type::template());

        $functional = Type::functional();

        $this->assertInstanceOf(Type::class, $functional);
        $this->assertSame('functional', (string) $functional);
        $this->assertSame($functional, Type::functional());
    }
}
