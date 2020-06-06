<?php
declare(strict_types = 1);

namespace Innmind\Genome\Gene;

use Innmind\Genome\{
    Gene,
    History,
    Exception\PreConditionFailed,
    Exception\ExpressionFailed,
};
use Innmind\OperatingSystem\OperatingSystem;
use Innmind\Server\Control\{
    Server,
    Server\Command,
    Server\Script,
    Exception\ScriptFailed,
};

final class ComposerPackage implements Gene
{
    private string $package;

    private function __construct(string $package)
    {
        $this->package = $package;
    }

    public static function global(string $package): self
    {
        return new self($package);
    }

    public function name(): string
    {
        return $this->package;
    }

    public function express(
        OperatingSystem $local,
        Server $target,
        History $history
    ): History {
        try {
            $preCondition = new Script(Command::foreground('which')->withArgument('composer'));
            $preCondition($target);
        } catch (ScriptFailed $e) {
            throw new PreConditionFailed('composer is missing');
        }

        try {
            $install = new Script(
                Command::foreground('composer')
                    ->withArgument('global')
                    ->withArgument('require')
                    ->withArgument($this->package),
            );
            $install($target);
        } catch (ScriptFailed $e) {
            throw new ExpressionFailed($this->name());
        }

        return $history;
    }
}
