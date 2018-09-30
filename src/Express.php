<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Genome\{
    Gene\Name,
    Gene\Type,
    Exception\UnknownGene,
    Exception\GeneExpressionFailed,
};
use Innmind\Url\PathInterface;
use Innmind\Server\Control\{
    Server,
    Server\Command,
};

final class Express
{
    private $genome;
    private $server;

    public function __construct(Genome $genome, Server $server)
    {
        $this->genome = $genome;
        $this->server = $server;
    }

    public function __invoke(Name $gene, PathInterface $path): void
    {
        if (!$this->genome->contains((string) $gene)) {
            throw new UnknownGene((string) $gene);
        }

        $gene = $this->genome->get((string) $gene);

        $this->deploy($gene, $path);
        $gene
            ->actions()
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
                    throw new GeneExpressionFailed((string) $gene->name());
                }
            });
    }

    private function deploy(Gene $gene, PathInterface $path): void
    {
        switch ($gene->type()) {
            case Type::template():
                $command = Command::foreground('composer')
                    ->withArgument('create-project')
                    ->withArgument((string) $gene->name())
                    ->withArgument((string) $path)
                    ->withOption('no-dev');
                break;

            case Type::functional():
            default:
                $command = Command::foreground('composer')
                    ->withArgument('global')
                    ->withArgument('require')
                    ->withArgument((string) $gene->name());
        }

        $process = $this
            ->server
            ->processes()
            ->execute($command)
            ->wait();

        if (!$process->exitCode()->isSuccessful()) {
            throw new GeneExpressionFailed((string) $gene->name());
        }
    }
}
