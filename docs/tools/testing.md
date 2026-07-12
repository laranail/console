# Testing

Helpers for testing code that renders this package's widgets or drives its
prompts. They live in `Simtabi\Laranail\Console\Testing` and ship with the
package (no dev-only install).

Most widgets are pure — `Console::box('Hi')->render()` returns a string you can
assert on directly. The two things a downstream test *can't* easily control are
**terminal capabilities** (colour / Unicode / width / interactivity) and
**prompt input**. The `InteractsWithConsole` trait covers both.

## The trait

```php
use Simtabi\Laranail\Console\Testing\InteractsWithConsole;

final class DeployCommandTest extends TestCase
{
    use InteractsWithConsole;

    public function test_renders_ascii_at_a_fixed_width(): void
    {
        $this->withConsoleCapabilities(colors: false, unicode: false, width: 40);

        $out = Console::box('Deploying…')->render();

        self::assertStringNotContainsString("\e[", $out); // no colour
        self::assertStringNotContainsString('─', $out);    // ASCII border
    }
}
```

`withConsoleCapabilities()` forces a deterministic profile; any argument left
`null` falls back to normal detection. It is restored automatically after each
test (via a PHPUnit `#[After]` hook), so fakes never leak between tests.

| Argument | Effect |
|----------|--------|
| `colors: false` | Disable ANSI colour (as if `NO_COLOR`) |
| `unicode: false` | Force the ASCII glyph set |
| `width: 40` | Pin the terminal width to 40 columns |
| `interactive: false` | Report a non-TTY (drives non-interactive fallbacks) |

## Scripting prompts

`withPromptInput()` is a thin delegate to laravel/prompts' `Prompt::fake()` —
pass the keystrokes to simulate:

```php
use Laravel\Prompts\Key;

$this->withPromptInput(['I', 'm', 'a', 'n', 'i', Key::ENTER]);

$name = Console::prompter()->text('Your name?')->getResult();

self::assertSame('Imani', $name);
```

## Without the trait

The same primitives are available directly:

```php
Simtabi\Laranail\Console\Tools\Support\Capabilities::fake(unicode: false, width: 80);
// … assertions …
Simtabi\Laranail\Console\Tools\Support\Capabilities::clearFake();
```

[← Docs index](../../README.md#documentation)
