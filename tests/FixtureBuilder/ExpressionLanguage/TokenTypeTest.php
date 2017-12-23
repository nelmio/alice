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
 * @covers \Nelmio\Alice\FixtureBuilder\ExpressionLanguage\TokenType
 */
class TokenTypeTest extends TestCase
{
    /**
     * @var string[]
     */
    private $constants;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $reflClass = new \ReflectionClass(TokenType::class);
        $this->constants = $reflClass->getConstants();
    }

    public function testIsImmutable()
    {
        $this->assertTrue(true, 'Nothing to do.');
    }

    /**
     * @testdox Test that the static values used to control the input are grouping all the constants.
     */
    public function testStaticValues()
    {
        $reflClass = new \ReflectionClass(TokenType::class);

        $reflProp = $reflClass->getProperty('values');
        $reflProp->setAccessible(true);
        $values = $reflProp->getValue(TokenType::class);

        $this->assertCount(count($this->constants), $values);
        foreach ($this->constants as $constant) {
            $this->assertTrue($values[$constant]);
        }
    }

    /**
     * @dataProvider provideAcceptableTypes
     */
    public function testCanCreateType(string $typeConstant)
    {
        $type = new TokenType($typeConstant);
        $this->assertEquals($type->getValue(), constant(sprintf('%s::%s', TokenType::class, $typeConstant)));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Expected type to be a known token type but got "unknown".
     */
    public function testThrowsAnExceptionIfAnInvalidTypeIsGiven()
    {
        new TokenType('unknown');
    }

    public function provideAcceptableTypes()
    {
        $reflClass = new \ReflectionClass(TokenType::class);
        $constants = $reflClass->getConstants();

        foreach ($constants as $constant) {
            yield [$constant];
        }
    }
}
