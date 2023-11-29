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

use PHPUnit\Framework\TestCase;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\Token
 * @internal
 */
class TokenTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $value = 'bob';
        $type = new TokenType(TokenType::DYNAMIC_ARRAY_TYPE);

        $token = new Token($value, $type);

        self::assertEquals($value, $token->getValue());
        self::assertEquals($type->getValue(), $token->getType());
        self::assertEquals('(DYNAMIC_ARRAY_TYPE) bob', $token->__toString());
    }

    /**
     * @depends test\Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenTypeTest::testIsImmutable
     */
    public function testIsImmutable(): void
    {
        self::assertTrue(true, 'Nothing to do.');
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $value = 'bob';
        $newValue = 'alice';
        $type = new TokenType(TokenType::DYNAMIC_ARRAY_TYPE);

        $token = new Token($value, $type);
        $newToken = $token->withValue($newValue);

        self::assertEquals($value, $token->getValue());
        self::assertEquals($type->getValue(), $token->getType());

        self::assertInstanceOf(Token::class, $newToken);
        self::assertEquals($newValue, $newToken->getValue());
        self::assertEquals($type->getValue(), $newToken->getType());
    }
}
