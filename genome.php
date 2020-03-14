<?php

use Innmind\Genome\{
    Genome,
    Gene,
};
use Innmind\Immutable\Sequence;

return function(): Genome {
    return new Genome(
        Gene::functional(
            new Gene\Name('innmind/installation-monitor'),
            Sequence::strings('installation-monitor oversee --daemon'),
            Sequence::strings(),
            Sequence::strings('installation-monitor kill')
        ),
        Gene::functional(
            new Gene\Name('innmind/infrastructure-neo4j'),
            Sequence::strings(
                'infrastructure-neo4j install',
                'infrastructure-neo4j setup-user'
            )
        ),
        Gene::functional(
            new Gene\Name('innmind/infrastructure-nginx'),
            Sequence::strings(
                'infrastructure-nginx install',
                'infrastructure-nginx setup-site'
            )
        ),
        Gene::functional(
            new Gene\Name('innmind/infrastructure-amqp'),
            Sequence::strings(
                'infrastructure-amqp install',
                'infrastructure-amqp setup-users'
            )
        ),
        Gene::functional(
            new Gene\Name('innmind/tower'),
            Sequence::strings('tower listen 1337 --daemon'),
            Sequence::strings('tower listen 1337 --daemon --restart')
        ),
        Gene::functional(
            new Gene\Name('innmind/warden'),
            Sequence::strings(
                'warden deploy',
                'warden grant Baptouuuu'
            )
        ),
        Gene::template(
            new Gene\Name('innmind/library'),
            Sequence::strings('bin/library install')
        ),
        Gene::template(
            new Gene\Name('innmind/crawler-app'),
            Sequence::strings('bin/crawler install')
        )
    );
};
