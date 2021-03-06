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

final class PHP implements Gene
{
    private string $version;
    /** @var list<string> */
    private array $extensions;

    public function __construct(int $major, int $minor, string ...$extensions)
    {
        $this->version = "$major.$minor";
        $this->extensions = $extensions;
    }

    public function name(): string
    {
        return "PHP {$this->version}";
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
                    ->withArgument("php@{$this->version}"),
            );
            $install($target);
        } catch (ScriptFailed $e) {
            throw new ExpressionFailed($this->name());
        }
    }

    /**
     * @see https://www.colinodell.com/blog/201911/how-to-install-php-74
     */
    private function linux(Server $target): void
    {
        try {
            $preCondition = new Script(
                Command::foreground('which')->withArgument('apt'),
                Command::foreground('which')->withArgument('add-apt-repository'),
            );
            $preCondition($target);
        } catch (ScriptFailed $e) {
            throw new PreConditionFailed('apt is missing');
        }

        $extensions = \array_map(
            fn(string $extension): string => "php{$this->version}-$extension",
            $this->extensions,
        );
        $aptInstall = Command::foreground('apt')
            ->withArgument('install')
            ->withShortOption('y')
            ->withArgument("php{$this->version}");
        $aptInstall = \array_reduce(
            $extensions,
            static fn(Command $install, string $extension): Command => $install->withArgument($extension),
            $aptInstall,
        );

        try {
            $install = new Script(
                Command::foreground('apt')
                    ->withArgument('install')
                    ->withShortOption('y')
                    ->withArgument('software-properties-common'),
                Command::foreground('add-apt-repository')
                    ->withArgument('ppa:ondrej/php'),
                Command::foreground('apt')
                    ->withArgument('update'),
                $aptInstall,
            );
            $install($target);
        } catch (ScriptFailed $e) {
            throw new ExpressionFailed($this->name());
        }
    }
}
