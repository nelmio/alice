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

/**
 * @covers Nelmio\Alice\ExpressionLanguage\Token
 */
class TokenTest extends \PHPUnit_Framework_TestCase
{
    public function testReadAccessorsReturnPropertiesValues()
    {
        $value = 'bob';
        $type = new TokenType(TokenType::DYNAMIC_ARRAY_TYPE);

        $token = new Token($value, $type);

        $this->assertEquals($value, $token->getValue());
        $this->assertEquals($type, $token->getType());
        $this->assertEquals('(DYNAMIC_ARRAY_TYPE) bob', $token->__toString());
    }

    public function testWithersReturnsNewModifiedInstance()
    {
        $value = 'bob';
        $newValue = 'alice';
        $type = new TokenType(TokenType::DYNAMIC_ARRAY_TYPE);

        $token = new Token($value, $type);
        $newToken = $token->withValue($newValue);

        $this->assertEquals($value, $token->getValue());
        $this->assertEquals($type, $token->getType());

        $this->assertInstanceOf(Token::class, $newToken);
        $this->assertEquals($newValue, $newToken->getValue());
        $this->assertEquals($type, $newToken->getType());
    }
}
