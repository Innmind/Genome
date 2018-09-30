<?php
declare(strict_types = 1);

namespace Innmind\Genome\Loader;

use Innmind\Genome\{
    Loader,
    Genome,
};
use Innmind\Url\PathInterface;

final class PHP implements Loader
{
    public function __invoke(PathInterface $path): Genome
    {
        $load = require (string) $path;

        return $load();
    }
}
