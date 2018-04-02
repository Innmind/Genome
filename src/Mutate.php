<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Genome\{
    Gene\Type,
    Exception\UnknownGene,
    Exception\GeneMutationFailed,
};
use Innmind\Url\PathInterface;
use Innmind\Server\Control\{
    Server,
    Server\Command,
};

final class Mutate
{
    private $genome;
    private $server;

    public function __construct(Genome $genome, Server $server)
    {
        $this->genome = $genome;
        $this->server = $server;
    }

    public function __invoke(string $gene, PathInterface $path): void
    {
        if (!$this->genome->contains($gene)) {
            throw new UnknownGene($gene);
        }

        $gene = $this->genome->get($gene);

        $this->update($gene, $path);
        $gene
            ->update()
            ->foreach(function(string $action) use ($gene, $path): void {
                $process = $this
                    ->server
                    ->processes()
                    ->execute(
                        Command::foreground($action)
                            ->withWorkingDirectory((string) $path)
                    )
                    ->wait();

                if (!$process->exitCode()->isSuccessful()) {
                    throw new GeneMutationFailed((string) $gene->name());
                }
            });
    }

    private function update(Gene $gene, PathInterface $path): void
    {
        switch ($gene->type()) {
            case Type::template():
                $command = Command::foreground('composer')
                    ->withArgument('update')
                    ->withWorkingDirectory((string) $path);
                break;

            case Type::functional():
            default:
                $command = Command::foreground('composer')
                    ->withArgument('global')
                    ->withArgument('update')
                    ->withArgument((string) $gene->name())
                    ->withWorkingDirectory((string) $path);
        }

        $process = $this
            ->server
            ->processes()
            ->execute($command)
            ->wait();

        if (!$process->exitCode()->isSuccessful()) {
            throw new GeneMutationFailed((string) $gene->name());
        }
    }
}
