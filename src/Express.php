<?php
declare(strict_types = 1);

namespace Innmind\Genome;

use Innmind\Genome\{
    Gene\Name,
    Gene\Type,
    Exception\UnknownGene,
    Exception\GeneExpressionFailed,
};
use Innmind\Url\Path;
use Innmind\Server\Control\{
    Server,
    Server\Command,
};

final class Express
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
        if (!$this->genome->contains($gene)) {
            throw new UnknownGene($gene->toString());
        }

        $gene = $this->genome->get($gene);

        $this->deploy($gene, $path);
        $gene
            ->actions()
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
                    throw new GeneExpressionFailed($gene->name()->toString());
                }
            });
    }

    private function deploy(Gene $gene, Path $path): void
    {
        switch ($gene->type()) {
            case Type::template():
                $command = Command::foreground('composer')
                    ->withArgument('create-project')
                    ->withArgument($gene->name()->toString())
                    ->withArgument($path->toString())
                    ->withOption('no-dev')
                    ->withOption('prefer-source')
                    ->withOption('keep-vcs');
                break;

            case Type::functional():
            default:
                $command = Command::foreground('composer')
                    ->withArgument('global')
                    ->withArgument('require')
                    ->withArgument($gene->name()->toString());
        }

        $process = $this
            ->server
            ->processes()
            ->execute($command);
        $process->wait();

        if (!$process->exitCode()->isSuccessful()) {
            throw new GeneExpressionFailed($gene->name()->toString());
        }
    }
}
