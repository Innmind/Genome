<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Loader;

use Innmind\Genome\{
    Loader\PHP,
    Loader,
    Genome,
    Gene\Type,
};
use Innmind\Url\Path;
use function Innmind\Immutable\unwrap;
use PHPUnit\Framework\TestCase;

class PHPTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Loader::class, new PHP);
    }

    public function testInvokation()
    {
        $load = new PHP;

        $genome = $load(Path::of('genome.php'));

        $this->assertInstanceOf(Genome::class, $genome);
        $this->assertTrue($genome->contains('innmind/infrastructure-neo4j'));
        $this->assertTrue($genome->contains('innmind/infrastructure-nginx'));
        $this->assertTrue($genome->contains('innmind/infrastructure-amqp'));
        $this->assertTrue($genome->contains('innmind/library'));
        $this->assertTrue($genome->contains('innmind/crawler-app'));
        $this->assertTrue($genome->contains('innmind/tower'));
        $this->assertTrue($genome->contains('innmind/warden'));
        $this->assertSame(Type::template(), $genome->get('innmind/library')->type());
        $this->assertSame(Type::functional(), $genome->get('innmind/tower')->type());
        $this->assertSame(
            ['tower listen 1337 --daemon'],
            unwrap($genome->get('innmind/tower')->actions())
        );
        $this->assertSame(
            ['tower listen 1337 --daemon --restart'],
            unwrap($genome->get('innmind/tower')->mutations())
        );
    }
}
