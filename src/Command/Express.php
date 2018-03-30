<?php
declare(strict_types = 1);

namespace Innmind\Genome\Command;

use Innmind\Genome\Express as Runner;
use Innmind\CLI\{
    Command,
    Command\Arguments,
    Command\Options,
    Environment,
};
use Innmind\Url\Path;

final class Express implements Command
{
    private $express;

    public function __construct(Runner $express)
    {
        $this->express = $express;
    }

    public function __invoke(Environment $env, Arguments $arguments, Options $options): void
    {
        ($this->express)(
            $arguments->get('gene'),
            new Path($arguments->get('path'))
        );
    }

    public function __toString(): string
    {
        return <<<USAGE
express gene path

Express the given gene in the specified path

When expressing a functional gene the path will be used as the working
directory when calling the associated actions so the path must exist otherwise
it will fail
USAGE;
    }
}
