# Genome

[![Build Status](https://github.com/Innmind/Genome/workflows/CI/badge.svg)](https://github.com/Innmind/Genome/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/Innmind/Genome/branch/develop/graph/badge.svg)](https://codecov.io/gh/Innmind/Genome)
[![Type Coverage](https://shepherd.dev/github/Innmind/Genome/coverage.svg)](https://shepherd.dev/github/Innmind/Genome)

Tool to facilitate the setup of machines.

The goal here is to provide a declarative way to setup machine without having to rely on config files that are not code. Here all the declaration is done via PHP so you can easily navigate from the declaration of a gene to the actual code being run.

Since it's standard PHP, you can easily require genes provided by other packages by requiring the packages via Composer. Look for the `innmind/genome` virtual package on [packagist](https://packagist.org/providers/innmind/genome) for more genes.

## Installation

```sh
composer global require innmind/genome
```

## Usage

```php
<?php
# genome.php

use Innmind\Genome\{
    Genome,
    Gene,
};

return new Genome(
    new Gene\PHP(7, 4),
    new Gene\Composer,
    Gene\ComposerPackage::global('innmind/installation-monitor'),
);
```

```sh
genome express path/to/genome.php --host=ssh://user@machine/
```

This will load the genome specified above and will sequencely install `php7.4`, `composer` and the package `innmind/silent-cartographer` as a global package.

You can omit the `--host` option and the install will happen on the local machine.

You can use this tool to automate the bootstrap of machines.
