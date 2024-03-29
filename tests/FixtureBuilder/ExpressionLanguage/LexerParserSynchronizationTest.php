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

namespace Nelmio\Alice\FixtureBuilder\ExpressionLanguage;

use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Lexer\LexerIntegrationTest;
use Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Parser\ParserIntegrationTest;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 * @internal
 */
class LexerParserSynchronizationTest extends TestCase
{
    public function testProvidesAreSynchronized(): void
    {
        $lexerTestCase = new LexerIntegrationTest();
        $lexerProviderKeys = [];
        foreach ($lexerTestCase->provideValues() as $key => $values) {
            $lexerProviderKeys[] = $key;
        }

        $parserTestCase = new ParserIntegrationTest();
        $parserProviderKeys = [];
        foreach ($parserTestCase->provideValues() as $key => $value) {
            $parserProviderKeys[] = $key;
        }

        foreach ($lexerProviderKeys as $index => $lexerProviderKey) {
            self::assertEquals($lexerProviderKey, $parserProviderKeys[$index]);
        }

        self::assertCount(count($lexerProviderKeys), $parserProviderKeys);
    }
}
