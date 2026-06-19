<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Security;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Formatting\ConsoleUIFormatter;
use Simtabi\Laranail\Console\Tools\Widgets\Table;

final class HardeningTest extends TestCase
{
    public function test_table_strips_control_characters_from_cells_and_headers(): void
    {
        $out = Table::make()
            ->headers(["Na\033[31mme"])
            ->rows([["cell\033]0;PWNED\x07end"], ["bel\x07"]])
            ->render();

        self::assertStringNotContainsString("\033", $out);
        self::assertStringNotContainsString("\x07", $out);
        self::assertStringContainsString('end', $out);
    }

    public function test_table_grouped_and_tree_inputs_are_sanitized(): void
    {
        $grouped = Table::make()->headers(['A'])->grouped(["g\x07rp" => [["x\033y"]]])->render();
        self::assertStringNotContainsString("\033", $grouped);
        self::assertStringNotContainsString("\x07", $grouped);

        $tree = Table::make()->headers(['Path'])->tree([[0, ["root\x07"]], [1, ["child\033X"]]])->render();
        self::assertStringNotContainsString("\033", $tree);
        self::assertStringNotContainsString("\x07", $tree);
    }

    public function test_add_text_color_href_honours_the_scheme_allow_list(): void
    {
        $bad = ConsoleUIFormatter::create()
            ->addMessage('click')
            ->addTextColor('green', isClickable: true, href: 'javascript:alert(1)')
            ->render();

        // A disallowed scheme must not become an OSC-8 link.
        self::assertStringNotContainsString('href=', $bad);
        self::assertStringNotContainsString('javascript:', $bad);

        $good = ConsoleUIFormatter::create()
            ->addMessage('docs')
            ->addTextColor('green', isClickable: true, href: 'https://example.com')
            ->render();

        self::assertStringContainsString('href=https://example.com', $good);
    }
}
