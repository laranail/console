<?php

declare(strict_types=1);

namespace Simtabi\Laranail\Console\Tools\Tests\Widgets;

use PHPUnit\Framework\TestCase;
use Simtabi\Laranail\Console\Tools\Widgets\ProgressBar;
use Symfony\Component\Console\Helper\ProgressBar as SymfonyProgressBar;
use Symfony\Component\Console\Output\BufferedOutput;

final class ProgressBarScopingTest extends TestCase
{
    /**
     * Regression: the flavoured bar must scope its custom placeholders to its
     * own instance, never registering them on Symfony's process-wide defaults
     * (which would poison every other progress bar in the host app).
     */
    public function test_does_not_register_global_placeholders(): void
    {
        self::assertNull(SymfonyProgressBar::getPlaceholderFormatterDefinition('rate'));

        new ProgressBar(new BufferedOutput, 10);

        self::assertNull(
            SymfonyProgressBar::getPlaceholderFormatterDefinition('rate'),
            'ProgressBar leaked a global "rate" placeholder',
        );
    }

    public function test_renders_with_eta_and_rate(): void
    {
        $out = new BufferedOutput;
        (new ProgressBar($out, 4))->format('detailed')->start()->advance(2)->finish();

        self::assertNotSame('', $out->fetch());
    }
}
