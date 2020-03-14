<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Genome\{
    Gene\Name,
    Gene\Type,
    Exception\UnknownGene,
    Exception\GeneSuppressionFailed,
};
use Innmind\Url\Path;
use Innmind\Server\Control\{
    Server,
    Server\Command,
};

final class Suppress
{
    private Genome $genome;
    private Server $server;

    public function __construct(Genome $genome, Server $server)
    {
        $this->genome = $genome;
        $this->server = $server;
    }

    public function __invoke(Name $gene, Path $path): void
    {
        if (!$this->genome->contains((string) $gene)) {
            throw new UnknownGene((string) $gene);
        }

        $gene = $this->genome->get((string) $gene);

        $gene
            ->suppressions()
            ->foreach(function(string $action) use ($gene, $path): void {
                $process = $this
                    ->server
                    ->processes()
                    ->execute(
                        Command::foreground($action)
                            ->withWorkingDirectory($path)
                    );
                $process->wait();

                if (!$process->exitCode()->isSuccessful()) {
                    throw new GeneSuppressionFailed((string) $gene->name());
                }
            });
        $this->delete($gene, $path);
    }

    private function delete(Gene $gene, Path $path): void
    {
        switch ($gene->type()) {
            case Type::template():
                $command = Command::foreground('rm')
                    ->withShortOption('r')
                    ->withArgument($path->toString());
                break;

            case Type::functional():
            default:
                $command = Command::foreground('composer')
                    ->withArgument('global')
                    ->withArgument('remove')
                    ->withArgument((string) $gene->name())
                    ->withWorkingDirectory($path);
        }

        $process = $this
            ->server
            ->processes()
            ->execute($command);
        $process->wait();

        if (!$process->exitCode()->isSuccessful()) {
            throw new GeneSuppressionFailed((string) $gene->name());
        }
    }
}
