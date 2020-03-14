<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Url\Path;

interface Loader
{
    public function __invoke(Path $path): Genome;
}
