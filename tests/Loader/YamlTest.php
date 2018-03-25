<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Loader;

use Innmind\Genome\{
    Loader\Yaml,
    Loader,
    Genome,
    Gene\Type,
};
use Innmind\Url\Path;
use PHPUnit\Framework\TestCase;

class YamlTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Loader::class, new Yaml);
    }

    public function testInvokation()
    {
        $load = new Yaml;

        $genome = $load(new Path('genome.yml'));

        $this->assertInstanceOf(Genome::class, $genome);
        $this->assertTrue($genome->contains('innmind/library'));
        $this->assertTrue($genome->contains('innmind/crawler-app'));
        $this->assertTrue($genome->contains('innmind/tower'));
        $this->assertSame(Type::template(), $genome->get('innmind/library')->type());
        $this->assertSame(Type::functional(), $genome->get('innmind/tower')->type());
        $this->assertSame(
            ['tower listen 1337 -d'],
            $genome->get('innmind/tower')->actions()->toPrimitive()
        );
    }
}
