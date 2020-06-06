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

final class Composer implements Gene
{
    public function name(): string
    {
        return 'Composer';
    }

    public function express(
        OperatingSystem $local,
        Server $target,
        History $history
    ): History {
        try {
            $this->macOS($target);
        } catch (PreConditionFailed $e) {
            $this->linux($target);
        }

        return $history;
    }

    private function macOS(Server $target): void
    {
        try {
            $preCondition = new Script(Command::foreground('which')->withArgument('brew'));
            $preCondition($target);
        } catch (ScriptFailed $e) {
            throw new PreConditionFailed('brew is missing');
        }

        try {
            $install = new Script(
                Command::foreground('brew')
                    ->withArgument('install')
                    ->withArgument('composer'),
            );
            $install($target);
        } catch (ScriptFailed $e) {
            throw new ExpressionFailed($this->name());
        }
    }

    private function linux(Server $target): void
    {
        try {
            $preCondition = new Script(
                Command::foreground('which')->withArgument('apt'),
            );
            $preCondition($target);
        } catch (ScriptFailed $e) {
            throw new PreConditionFailed('apt is missing');
        }

        try {
            $install = new Script(
                Command::foreground('apt')
                    ->withArgument('install')
                    ->withShortOption('y')
                    ->withArgument('composer'),
            );
            $install($target);
        } catch (ScriptFailed $e) {
            throw new ExpressionFailed($this->name());
        }
    }
}
