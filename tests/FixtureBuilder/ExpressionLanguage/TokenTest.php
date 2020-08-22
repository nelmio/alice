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
 */
class TokenTest extends TestCase
{
    public function testReadAccessorsReturnPropertiesValues(): void
    {
        $value = 'bob';
        $type = new TokenType(TokenType::DYNAMIC_ARRAY_TYPE);

        $token = new Token($value, $type);

        static::assertEquals($value, $token->getValue());
        static::assertEquals($type->getValue(), $token->getType());
        static::assertEquals('(DYNAMIC_ARRAY_TYPE) bob', $token->__toString());
    }

    /**
     * @depends \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenTypeTest::testIsImmutable
     */
    public function testIsImmutable(): void
    {
        static::assertTrue(true, 'Nothing to do.');
    }

    public function testWithersReturnNewModifiedInstance(): void
    {
        $value = 'bob';
        $newValue = 'alice';
        $type = new TokenType(TokenType::DYNAMIC_ARRAY_TYPE);

        $token = new Token($value, $type);
        $newToken = $token->withValue($newValue);

        static::assertEquals($value, $token->getValue());
        static::assertEquals($type->getValue(), $token->getType());

        static::assertInstanceOf(Token::class, $newToken);
        static::assertEquals($newValue, $newToken->getValue());
        static::assertEquals($type->getValue(), $newToken->getType());
    }
}
