# Genome

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/Genome/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Genome/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/Genome/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Genome/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/Genome/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Genome/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/Genome/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Genome/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/Genome/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/Genome/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/Genome/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/Genome/build-status/develop) |

Tool to facilitate installation of composer projects on a server

## Installation

```sh
composer global require innmind/genome
```

## Usage

```sh
genome express innmind/installation-monitor ~/
```

This will load the [genome](genome.php) from this project and then will require the `innmind/installation-monitor` as a global dependency and call `installation-monitor oversee --daemon`

You can use your own genome by specifying the path to yours in the `GENOME` env variable before calling the cli tool.

`genome mutate` will update all the expressed genes on the machine.

`genome suppress innmind/installation-monitor ~/` will delete the dependency.
