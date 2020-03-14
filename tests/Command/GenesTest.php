<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome\Command;

use Innmind\Genome\{
    Command\Genes,
    Genome,
    Gene,
    Gene\Name,
};
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Stream\Writable;
use Innmind\Immutable\{
    Str,
    Sequence,
};
use PHPUnit\Framework\TestCase;

class GenesTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Command::class,
            new Genes(new Genome)
        );
    }

    public function testUsage()
    {
        $expected = <<<USAGE
genes

List all the genes that can be expressed
USAGE;

        $this->assertSame($expected, (new Genes(new Genome))->toString());
    }

    public function testInvokation()
    {
        $command = new Genes(new Genome(
            Gene::template(new Name('foo/bar'), Sequence::of('string'), Sequence::of('string')),
            Gene::template(new Name('bar/baz'), Sequence::of('string'), Sequence::of('string'))
        ));
        $env = $this->createMock(Environment::class);
        $env
            ->expects($this->exactly(2))
            ->method('output')
            ->willReturn($output = $this->createMock(Writable::class));
        $output
            ->expects($this->at(0))
            ->method('write')
            ->with(Str::of("foo/bar\n"));
        $output
            ->expects($this->at(1))
            ->method('write')
            ->with(Str::of("bar/baz\n"));
        $output
            ->expects($this->exactly(2))
            ->method('write');

        $this->assertNull($command(
            $env,
            new Arguments,
            new Options
        ));
    }
}
