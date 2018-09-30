<?php

use Innmind\Genome\{
    Genome,
    Gene,
};
use Innmind\Immutable\Stream;

return function(): Genome {
    return new Genome(
        Gene::functional(
            new Gene\Name('innmind/installation-monitor'),
            Stream::of('string', 'installation-monitor oversee --daemon')
        ),
        Gene::functional(
            new Gene\Name('innmind/infrastructure-neo4j'),
            Stream::of(
                'string',
                'infrastructure-neo4j install',
                'infrastructure-neo4j setup-user'
            )
        ),
        Gene::functional(
            new Gene\Name('innmind/infrastructure-nginx'),
            Stream::of(
                'string',
                'infrastructure-nginx install',
                'infrastructure-nginx setup-site'
            )
        ),
        Gene::functional(
            new Gene\Name('innmind/infrastructure-amqp'),
            Stream::of(
                'string',
                'infrastructure-amqp install',
                'infrastructure-amqp setup-users'
            )
        ),
        Gene::functional(
            new Gene\Name('innmind/tower'),
            Stream::of('string', 'tower listen 1337 --daemon'),
            Stream::of('string', 'tower listen 1337 --daemon --restart')
        ),
        Gene::functional(
            new Gene\Name('innmind/warden'),
            Stream::of(
                'string',
                'warden deploy',
                'warden grant Baptouuuu'
            )
        ),
        Gene::template(
            new Gene\Name('innmind/library'),
            Stream::of('string', 'bin/library install')
        ),
        Gene::template(
            new Gene\Name('innmind/crawler-app'),
            Stream::of('string', 'bin/crawler install')
        )
    );
};
