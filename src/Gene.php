<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Genome\Exception\{
    PreConditionFailed,
    ExpressionFailed,
};
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Server\Control\Server;

interface Gene
{
    public function name(): string;

    /**
     * @throws PreConditionFailed
     */
    public function canBeExpressedAt(Server $target): void;

    /**
     * @throws ExpressionFailed
     */
    public function express(OperatingSystem $local, Server $target): void;
}
