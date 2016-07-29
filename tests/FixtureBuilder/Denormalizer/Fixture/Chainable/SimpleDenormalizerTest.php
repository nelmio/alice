<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\Definition\Fixture\FixtureWithFlags;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Flag\TemplateFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\MethodCallBag;
use Nelmio\Alice\Definition\PropertyBag;
use Nelmio\Alice\Definition\SpecificationBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\FakeSpecificationBagDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationsDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FakeFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleDenormalizer
 */
class SimpleDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAChainableDenormalizer()
    {
        $this->assertTrue(is_a(SimpleDenormalizer::class, ChainableFixtureDenormalizerInterface::class, true));
    }

    public function testIsAFlagParserAwareDenormalizer()
    {
        $this->assertTrue(is_a(SimpleDenormalizer::class, FlagParserAwareInterface::class, true));
    }

    public function testCanBeInstantiatedWithAFlagParser()
    {
        new SimpleDenormalizer(new FakeSpecificationBagDenormalizer(), new FakeFlagParser());
    }

    public function testCanBeInstantiatedWithoutAFlagParser()
    {
        new SimpleDenormalizer(new FakeSpecificationBagDenormalizer());
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        clone new SimpleDenormalizer(new FakeSpecificationBagDenormalizer());;
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Expected method "Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\SimpleDenormalizer::checkFlagParser" to be called only if it has a flag parser.
     */
    public function testCannotDenormalizeFixtureIfHasNoFlagParser()
    {
        /** @var SpecificationsDenormalizerInterface $specsDenormalizer */
        $specsDenormalizer = $this->prophesize(SpecificationsDenormalizerInterface::class)->reveal();

        $denormalizer = new SimpleDenormalizer($specsDenormalizer);
        $denormalizer->denormalize(new FixtureBag(), 'Nelmio\Alice\Entity\User', 'user0', [], new FlagBag(''));
    }

    public function testDenormalizesValuesToCreateANewFixtureObjectAndAddItToTheListOfFixturesReturned()
    {
        $fixtures = new FixtureBag();
        $className = 'Nelmio\Entity\User';
        $reference = 'user_base';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = new FlagBag('');

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy
            ->parse($reference)
            ->willReturn(new FlagBag('user_base'))
        ;
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $specsDenormalizerProphecy = $this->prophesize(SpecificationsDenormalizerInterface::class);
        $expectedSpecs = new SpecificationBag(null, new PropertyBag(), new MethodCallBag());
        $specsDenormalizerProphecy
            ->denormalize(Argument::type(SimpleFixture::class), $flagParser, $specs)
            ->willReturn($expectedSpecs)
        ;
        /** @var SpecificationsDenormalizerInterface $specsDenormalizer */
        $specsDenormalizer = $specsDenormalizerProphecy->reveal();

        $denormalizer = (new SimpleDenormalizer($specsDenormalizer))->withParser($flagParser);
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        $expected = $fixtures->with(
            new FixtureWithFlags(
                new SimpleFixture(
                    $reference,
                    $className,
                    $expectedSpecs
                ),
                new FlagBag('user_base')
            )
        );

        $this->assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $specsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    public function testDenormalizationKeepsFlagsInIds()
    {
        $fixtures = new FixtureBag();
        $className = 'Nelmio\Entity\User';
        $reference = 'user_base (template)';
        $specs = [
            'username' => '<name()>',
        ];
        $flags = new FlagBag('');

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy
            ->parse($reference)
            ->willReturn(
                (new FlagBag('user_base'))->with(new TemplateFlag())
            )
        ;
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $specsDenormalizerProphecy = $this->prophesize(SpecificationsDenormalizerInterface::class);
        $expectedSpecs = new SpecificationBag(null, new PropertyBag(), new MethodCallBag());
        $specsDenormalizerProphecy
            ->denormalize(Argument::type(SimpleFixture::class), $flagParser, $specs)
            ->willReturn($expectedSpecs)
        ;
        /** @var SpecificationsDenormalizerInterface $specsDenormalizer */
        $specsDenormalizer = $specsDenormalizerProphecy->reveal();

        $denormalizer = (new SimpleDenormalizer($specsDenormalizer))->withParser($flagParser);
        $actual = $denormalizer->denormalize($fixtures, $className, $reference, $specs, $flags);

        $expected = $fixtures->with(
            new FixtureWithFlags(
                new SimpleFixture(
                    'user_base',
                    $className,
                    $expectedSpecs
                ),
                (new FlagBag('user_base'))->with(new TemplateFlag())
            )
        );

        $this->assertEquals($expected, $actual);

        $flagParserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $specsDenormalizerProphecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }
}
