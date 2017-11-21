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

<<<<<<< HEAD
=======
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ParserIntegrationTest;
>>>>>>> parent of e51d18e... [cs] drop unused brackets, spaces, order imports
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\IsolatedSymfonyBuiltInParser;

/**
 * @group integration
 * @coversNothing
 */
class ParserIntegrationTest extends \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ParserIntegrationTest
{
    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->parser = new IsolatedSymfonyBuiltInParser();
    }
}
