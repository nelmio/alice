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
    public function testReadAccessorsReturnPropertiesValues()
    {
        $value = 'bob';
        $type = new TokenType(TokenType::DYNAMIC_ARRAY_TYPE);

        $token = new Token($value, $type);

        $this->assertEquals($value, $token->getValue());
        $this->assertEquals($type->getValue(), $token->getType());
        $this->assertEquals('(DYNAMIC_ARRAY_TYPE) bob', $token->__toString());
    }

    /**
     * @depends Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenTypeTest::testIsImmutable
     */
    public function testIsImmutable()
    {
        $this->assertTrue(true, 'Nothing to do.');
    }

    public function testWithersReturnNewModifiedInstance()
    {
        $value = 'bob';
        $newValue = 'alice';
        $type = new TokenType(TokenType::DYNAMIC_ARRAY_TYPE);

        $token = new Token($value, $type);
        $newToken = $token->withValue($newValue);

        $this->assertEquals($value, $token->getValue());
        $this->assertEquals($type->getValue(), $token->getType());

        $this->assertInstanceOf(Token::class, $newToken);
        $this->assertEquals($newValue, $newToken->getValue());
        $this->assertEquals($type->getValue(), $newToken->getType());
    }
}
