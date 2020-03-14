<?php
declare(strict_types = 1);

namespace Innmind\Genome\Loader;

use Innmind\Genome\{
    Loader,
    Genome,
};
use Innmind\Url\Path;

final class PHP implements Loader
{
    public function __invoke(Path $path): Genome
    {
        $load = require $path->toString();

        return $load();
    }
}
