<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Url\PathInterface;
use Innmind\CLI\Commands;
use Innmind\Filesystem\Adapter;
use Innmind\Server\Control\ServerFactory;

function bootstrap(PathInterface $genome, Adapter $storage):Commands
{
    $server = ServerFactory::build();

    $genome = Genome::load(new Loader\PHP, $genome);
    $express = new Express($genome, $server);

    return new Commands(
        new Command\Express($express, $storage),
        new Command\Mutate(
            new Mutate($genome, $server),
            $storage
        ),
        new Command\Suppress(
            new Suppress($genome, $server),
            $storage
        ),
        new Command\Genes($genome)
    );
}
