# Roadmap

`laranail/console` is **1.x stable** under [SemVer](docs/release.md#versioning--stability).
This roadmap is intentionally lightweight and **community-driven**:

- 💡 **Ideas / proposals** → [GitHub Discussions](https://github.com/laranail/console/discussions)
- 🐞 **Bugs** → [GitHub Issues](https://github.com/laranail/console/issues)
- 🔒 **Security** → privately per [SECURITY.md](SECURITY.md) (`opensource@simtabi.com`)

Nothing here is a commitment or a date — it's a snapshot of direction. Items move
between sections as work happens.

## Now (1.x)

- Keep the public API stable; only additive changes in minor releases.
- Documentation accuracy + examples coverage as features land.
- Dependency hygiene via Dependabot (patch/minor auto-merged on green CI).

## Next (candidates)

- More syntax-highlighting languages on request (open a Discussion with the language).
- More theme presets (propose a palette in a Discussion).
- Optional chart refinements (axis ticks, legends) driven by real use cases.

## Considering

- Extracting the experimental `symfony/tui` integration into a companion package
  so the core can drop `minimum-stability: dev`.
- A rendered documentation site at the package URL.

## Shipped (highlights)

See [CHANGELOG.md](CHANGELOG.md) for the full history. Recent: design system + charts
(bar/column/line/scatter/heatmap/histogram/stacked), Markdown rendering, theme presets,
config validation (`laranail::console.check`), benchmarks, and the
`InteractsWithConsoleServices` command trait.

---

Want something here? **Start a [Discussion](https://github.com/laranail/console/discussions)** —
that's how items get added.
