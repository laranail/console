# Changelog

All notable changes to `laranail/console` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- **`ProgressReporter` seam** (`Console\Progress`) — a renderer-agnostic way to
  report multi-step progress. `PromptsProgressReporter` (laravel/prompts) is the
  default and works everywhere; `TuiProgressReporter` renders via the experimental
  **symfony/tui** full-screen component when `console.tui.progress` is enabled **and**
  symfony/tui is installed (it stays an optional `suggest`). Resolve `ProgressReporter`
  from the container: `app(ProgressReporter::class)->run($label, $steps, $callback)`.

## [1.0.0] - 2026-06-26

Initial stable release — a CLI design system: theme, typography, document composer,
widgets/charts, an interactive/live layer, and the `Prompter` input suite, over the
two decoupled sub-domains `Console\Tools` (output) and `Console\Prompter` (input).
