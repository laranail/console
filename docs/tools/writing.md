# Console writer

`ConsoleWriter` (under `Simtabi\Laranail\Console\Tools\Services`) is a **fluent,
immutable** wrapper over a Symfony `OutputInterface`. Where
[`ConsoleUIFormatter`](formatting.md) returns *strings*, `ConsoleWriter` styles **and
writes** — chainable, with ready-to-use **context statuses** and **emoji** support.
Every styling/config method returns a new instance, so a configured writer is safe to
reuse.

## Getting one

```php
use Simtabi\Laranail\Console\Facades\Console;

Console::writer($output);   // facade (defaults to a fresh ConsoleOutput)
console_writer($output);    // global helper

// inside a command (the enhanced base, or via the trait):
$this->consoleWriter();     // bound to the command's own output
```

To use the writer on a command that extends a different base, `use` the trait:

```php
use Simtabi\Laranail\Console\Tools\Commands\Concerns\InteractsWithConsoleWriter;
```

## Writing & styling

```php
$w = console_writer($output);

$w->line('plain line');
$w->write('no newline')->newLine();
$w->color('green')->bold()->line('styled');     // fg + options
$w->background('red')->line('on red');
$w->style('comment')->line('a Symfony style');  // raw Symfony output style
$w->plain()->line('clears styling');

$w->verbosity(OutputInterface::VERBOSITY_VERBOSE)->line('only with -v');
$w->toStderr()->line('to stderr');
$w->escaped()->line('<not a tag>');             // escape Symfony markup
echo $w->color('cyan')->format('hi');           // return the markup string without writing
```

Styling is immutable — `$w->color('green')` returns a *new* writer; the original is
unchanged.

## Context statuses

Ready-to-use, coloured glyph + message (rendered via
[`StatusLine`](widgets.md); glyphs degrade to ASCII without Unicode). `error()` and
`danger()` are written to **stderr**.

```php
$w->success('Deployed');   // ✓ green   ([OK] without Unicode)
$w->info('Building…');      // ℹ blue
$w->note('Heads up');       // ● cyan
$w->warning('Low disk');    // ⚠ yellow
$w->pending('Queued');      // ○ gray
$w->error('Failed');        // ✗ red    → stderr
$w->danger('Data loss!');   // ✖ red    → stderr

$w->success('a', 'b', 'c'); // one status line per message
```

## Emoji & symbols

Add a leading emoji/glyph, or let inline `:shortcode:` markers render
([Emoji](emoji.md) / [Symbols](symbols.md), capability-aware):

```php
$w->emoji('rocket')->line('Launching');     // 🚀 Launching   (Emoji name)
$w->emoji(':fire:')->line('Hot');           // 🔥 Hot         (shortcode)
$w->emoji('✅')->line('Custom');            // ✅ Custom      (literal passthrough)
$w->symbol('arrow')->line('Next');          // → Next         (Symbols glyph)

$w->line('Done :tada:');                    // Done 🎉        (inline shortcode)
$w->emojis(false)->line('Keep :tada:');     // Keep :tada:    (disable inline rendering)
```

Glyph/emoji variants follow the detected terminal [Capabilities](support.md#capabilities);
inject your own for deterministic tests with `->capabilities(Capabilities::fake(...))`.

[← Docs index](../../README.md#documentation)
