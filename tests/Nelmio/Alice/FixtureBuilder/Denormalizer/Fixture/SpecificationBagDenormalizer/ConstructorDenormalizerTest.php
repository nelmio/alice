<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCall\MethodCallWithReference;
use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\ServiceReference\InstantiatedReference;
use Nelmio\Alice\Definition\ServiceReference\StaticReference;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\ConstructorDenormalizer
 */
class ConstructorDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConstructorDenormalizer
     */
    private $denormalizer;

    public function setUp()
    {
        $this->denormalizer = new ConstructorDenormalizer();
    }

    public function testDenormalizeSimpleArguments()
    {
        $unparsedConstructor = [
            '<latitude()>',
            '1 (unique)' => '<longitude()>',
            '2' => '<random()>',
        ];

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn('dummy');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $arg1Flags = (new FlagBag('1'));
        $flagParserProphecy->parse('1 (unique)')->willReturn($arg1Flags);
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $expected = new SimpleMethodCall(
            '__construct',
            [
                '<latitude()>',
                '1' => '<longitude()>',
                '2' => '<random()>',
            ]
        );
        
        $actual = $this->denormalizer->denormalize($fixture, $flagParser, $unparsedConstructor);

        $this->assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testDenormalizeFirstArgumentKeyAsStringIndex()
    {
        $unparsedConstructor = [
            '0' => '<latitude()>',
        ];

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn('dummy');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $expected = new SimpleMethodCall(
            '__construct',
            [
                '<latitude()>',
            ]
        );

        $actual = $this->denormalizer->denormalize($fixture, $flagParser, $unparsedConstructor);
        $this->assertEquals($expected, $actual);
    }

    public function testDenormalizeWithSelfStaticConstructor()
    {
        $unparsedConstructor = [
            'create' => [
                '<latitude()>',
                '1 (unique)' => '<longitude()>',
            ]
        ];

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getClassName()->willReturn('Nelmio\Entity\User');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $arg1Flags = (new FlagBag('1'));
        $flagParserProphecy->parse('1 (unique)')->willReturn($arg1Flags);
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $expected = new MethodCallWithReference(
            new StaticReference('Nelmio\Entity\User'),
            'create',
            [
                '<latitude()>',
                '1' => '<longitude()>',
            ]
        );

        $actual = $this->denormalizer->denormalize($fixture, $flagParser, $unparsedConstructor);

        $this->assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testDenormalizeWithAnotherStaticConstructor()
    {
        $unparsedConstructor = [
            'Nelmio\Entity\UserFactory::create' => [
                '<latitude()>',
                '1 (unique)' => '<longitude()>',
            ]
        ];

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getClassName()->willReturn('Nelmio\Entity\User');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $arg1Flags = (new FlagBag('1'));
        $flagParserProphecy->parse('1 (unique)')->willReturn($arg1Flags);
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $expected = new MethodCallWithReference(
            new StaticReference('Nelmio\Entity\UserFactory'),
            'create',
            [
                '<latitude()>',
                '1' => '<longitude()>',
            ]
        );

        $actual = $this->denormalizer->denormalize($fixture, $flagParser, $unparsedConstructor);

        $this->assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testDenormalizeWithAnExternalConstructor()
    {
        $unparsedConstructor = [
            '@nelmio.entity.user_factory::create' => [
                '<latitude()>',
                '1 (unique)' => '<longitude()>',
            ]
        ];

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getClassName()->willReturn('Nelmio\Entity\User');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $arg1Flags = (new FlagBag('1'));
        $flagParserProphecy->parse('1 (unique)')->willReturn($arg1Flags);
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $expected = new MethodCallWithReference(
            new InstantiatedReference('nelmio.entity.user_factory'),
            'create',
            [
                '<latitude()>',
                '1' => '<longitude()>',
            ]
        );

        $denormalizer = new ConstructorDenormalizer();
        $actual = $denormalizer->denormalize($fixture, $flagParser, $unparsedConstructor);

        $this->assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid constructor method "Nelmio\Entity\UserFactory::create::something".
     */
    public function testDenormalizeWithInvalidConstructor()
    {
        $unparsedConstructor = [
            'Nelmio\Entity\UserFactory::create::something' => [
                '<latitude()>',
                '1 (unique)' => '<longitude()>',
            ]
        ];

        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn('dummy');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $arg1Flags = (new FlagBag('1'));
        $flagParserProphecy->parse('1 (unique)')->willReturn($arg1Flags);
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $this->denormalizer->denormalize($fixture, $flagParser, $unparsedConstructor);
    }
}
