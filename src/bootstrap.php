<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Url\Path;
use Innmind\CLI\Commands;
use Innmind\Filesystem\Adapter;
use Innmind\Server\Control\Server;
use Innmind\HttpTransport\Transport;

function bootstrap(
    Path $genome,
    Adapter $storage,
    Server $server,
    Transport $http
):Commands {
    $load = new Loader\Packagist($http);
    $genome = $load($genome);
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
