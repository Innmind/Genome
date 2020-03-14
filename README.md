# Genome

[![Build Status](https://github.com/Innmind/Genome/workflows/CI/badge.svg)](https://github.com/Innmind/Genome/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/Innmind/Genome/branch/develop/graph/badge.svg)](https://codecov.io/gh/Innmind/Genome)
[![Type Coverage](https://shepherd.dev/github/Innmind/Genome/coverage.svg)](https://shepherd.dev/github/Innmind/Genome)

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
