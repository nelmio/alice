<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\ExpressionLanguage;

use Nelmio\Alice\ExpressionLanguage\Lexer\LexerRegistryTest;
use Nelmio\Alice\ExpressionLanguage\Parser\SimpleParserTest;

/**
 * @coversNothing
 */
class LexerParserSynchronizationTest extends \PHPUnit_Framework_TestCase
{
    public function testProvidesAreSynchronized()
    {
        $lexerTestCase = new LexerRegistryTest();
        $lexerProviderKeys = [];
        foreach ($lexerTestCase->provideValues() as $key => $values) {
            $lexerProviderKeys[] = $key;
        }

        $parserTestCase = new SimpleParserTest();
        $parserProviderKeys = [];
        foreach ($parserTestCase->provideValues() as $key => $value) {
            $parserProviderKeys[] = $key;
        }

        $this->assertEquals($lexerProviderKeys, $parserProviderKeys);
        $this->assertEquals($parserProviderKeys, $lexerProviderKeys);
    }
}
