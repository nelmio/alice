<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Bridge\Symfony\FixtureBuilder\ExpressionLanguage\Parser;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\IsolatedSymfonyBuiltInParser;

/**
 * @group integration
 * @group symfony
 */
class ParserIntegrationTest extends \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ParserIntegrationTest
{
    public function setUp()
    {
        $this->parser = new IsolatedSymfonyBuiltInParser();
    }
}
