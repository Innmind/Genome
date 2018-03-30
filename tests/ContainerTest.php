<?php
declare(strict_types = 1);

namespace Tests\Innmind\Genome;

use Innmind\Genome\Express;
use Innmind\Compose\ContainerBuilder\ContainerBuilder;
use Innmind\CLI\Commands;
use Innmind\Url\Path;
use Innmind\Immutable\Map;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testLoad()
    {
        $container = (new ContainerBuilder)(
            new Path('container.yml'),
            (new Map('string', 'mixed'))
                ->put('genomePath', new Path('genome.yml'))
                ->put('pathToStoreExpressedGenes', '/tmp/innmind')
        );

        $this->assertTrue($container->has('commands'));
        $this->assertTrue($container->has('express'));
        $this->assertInstanceOf(Commands::class, $container->get('commands'));
        $this->assertInstanceOf(Express::class, $container->get('express'));
    }
}
