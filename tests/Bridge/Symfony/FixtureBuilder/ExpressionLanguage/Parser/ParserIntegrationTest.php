<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\Bridge\Symfony\FixtureBuilder\ExpressionLanguage\Parser;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\IsolatedSymfonyBuiltInParser;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ParserIntegrationTest as CoreParserIntegrationTest;

/**
 * @internal
 */
#[\PHPUnit\Framework\Attributes\Group('integration')]
#[\PHPUnit\Framework\Attributes\CoversNothing]
final class ParserIntegrationTest extends CoreParserIntegrationTest
{
    protected function setUp(): void
    {
        $this->parser = new IsolatedSymfonyBuiltInParser();
    }
}
