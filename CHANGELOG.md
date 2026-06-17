# Changelog

All notable changes to `laranail/console-tools` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Initial extraction of the laranail console toolkit from `laranail/laranail`
  core into an independent package. Targets PHP `^8.3` (8.3–8.5) and
  Laravel `^13.0`.
- `Formatting\ConsoleUIFormatter` — fluent Symfony Console output formatter
  with colors, badges, status lines, tree structures and statistics.
- `Formatting\ConsoleProgressBar` — Symfony ProgressBar wrapper with
  memory tracking and formatted status output.
- `Formatting\SeederConsoleFormatter` (+ contract) — tree-structured console
  output for database seeding operations.
- `Commands\LaranailCommand` — enhanced Artisan command base with a
  service-based architecture (performance, events, signals, metadata,
  logging, errors, configuration, interaction, display).
- `Commands\Services\*` — the nine command services + `CommandServiceManager`.
- `Observers\ConsoleCommandObserver`, `Runners\ConsoleRunner`,
  `Events\CommandEvents`.
- `Concerns\HasDisplayFormatting`, `Concerns\HasConfigurationAccess`.
