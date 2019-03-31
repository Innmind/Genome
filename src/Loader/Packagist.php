<?php
declare(strict_types = 1);

namespace Innmind\Genome\Loader;

use Innmind\Genome\{
    Loader,
    Genome,
    Gene,
    Exception\DomainException,
};
use Innmind\Url\{
    Url,
    PathInterface,
};
use Innmind\HttpTransport\Transport;
use Innmind\Http\{
    Message\Request\Request,
    Message\Method\Method,
    ProtocolVersion\ProtocolVersion,
};
use Innmind\Json\Json;
use Innmind\Immutable\{
    Map,
    Str,
    Stream,
};
use Composer\Semver\{
    VersionParser,
    Semver,
};

final class Packagist implements Loader
{
    private $fulfill;

    public function __construct(Transport $fulfill)
    {
        $this->fulfill = $fulfill;
    }

    public function __invoke(PathInterface $path): Genome
    {
        return Genome::defer($this->load($path));
    }

    private function load(PathInterface $path): \Generator
    {
        $name = Str::of((string) $path)->leftTrim('/');
        $url = "https://packagist.org/search.json?q=$name/";
        $results = [];

        do {
            $request = new Request(
                Url::fromString($url),
                Method::get(),
                new ProtocolVersion(2, 0)
            );
            $response = ($this->fulfill)($request);
            $content = Json::decode((string) $response->body());
            $results = \array_merge($results, $content['results']);
            $url = $content['next'] ?? null;
        } while (isset($content['next']));

        foreach ($results as $result) {
            if (!Str::of($result['name'])->matches("~^$name/~")) {
                continue;
            }

            if ($result['virtual'] ?? false === true) {
                continue;
            }

            $request = new Request(
                Url::fromString("https://packagist.org/packages/{$result['name']}.json"),
                Method::get(),
                new ProtocolVersion(2, 0)
            );
            $response = ($this->fulfill)($request);
            $content = Json::decode((string) $response->body())['package'];

            try {
                yield $this->geneOf($content);
            } catch (DomainException $e) {
                continue;
            }
        }
    }

    private function geneOf(array $package): Gene
    {
        $versions = $package['versions'];
        $published = Map::of(
            'string',
            'array',
            \array_keys($versions),
            \array_values($versions)
        )
            ->filter(static function(string $version): bool {
                return VersionParser::parseStability($version) === 'stable';
            })
            ->filter(static function(string $_, array $version): bool {
                return !($version['abandoned'] ?? false);
            });

        if ($published->size() === 0) {
            throw new DomainException;
        }

        $versions = Semver::rsort($published->keys()->toPrimitive());

        $latest = $published->get($versions[0]);

        if ($latest['type'] === 'project') {
            return Gene::template(
                new Gene\Name($latest['name']),
                Stream::of('string', ...($latest['extra']['gene']['expression'] ?? [])),
                Stream::of('string', ...($latest['extra']['gene']['mutation'] ?? [])),
                Stream::of('string', ...($latest['extra']['gene']['suppression'] ?? []))
            );
        }

        if (
            $latest['type'] === 'library' &&
            isset($latest['bin'])
        ) {
            return Gene::functional(
                new Gene\Name($latest['name']),
                Stream::of('string', ...($latest['extra']['gene']['expression'] ?? [])),
                Stream::of('string', ...($latest['extra']['gene']['mutation'] ?? [])),
                Stream::of('string', ...($latest['extra']['gene']['suppression'] ?? []))
            );
        }

        throw new DomainException;
    }
}
