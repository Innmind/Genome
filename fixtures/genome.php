<?php

use Innmind\Genome\{
    Genome,
    Gene,
    History,
};
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Server\Control\Server;

return new Genome(
    new class implements Gene {
        public function name(): string
        {
            return 'hello';
        }

        public function express(
            OperatingSystem $local,
            Server $target,
            History $history
        ): History {
            return $history;
        }
    },
);
