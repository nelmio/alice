<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\FakeChainableParserAwareDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerRegistry
 */
class FixtureDenormalizerRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $propRefl;

    public function setUp()
    {
        $propRelf = (new \ReflectionClass(FixtureDenormalizerRegistry::class))->getProperty('denormalizers');
        $propRelf->setAccessible(true);

        $this->propRefl = $propRelf;
    }

    public function testIsADenormalizer()
    {
        $this->assertTrue(is_a(FixtureDenormalizerRegistry::class, FixtureDenormalizerInterface::class, true));
    }

    public function testOnlyAcceptsChainableFixtureDenormalizers()
    {
        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        try {
            new FixtureDenormalizerRegistry($flagParser, [new \stdClass()]);
            $this->fail('Expected exception to be thrown.');
        } catch (\TypeError $error) {
            $this->assertEquals(
                'Expected denormalizer 0 to be a '
                .'"Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface", got '
                .'"stdClass" instead.',
                $error->getMessage()
            );
        }

        try {
            new FixtureDenormalizerRegistry($flagParser, [1]);
            $this->fail('Expected exception to be thrown.');
        } catch (\TypeError $error) {
            $this->assertEquals(
                'Expected denormalizer 0 to be a '
                .'"Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface", got '
                .'"integer" instead.',
                $error->getMessage()
            );
        }
    }
    
    public function testInjectsParserInParserAwareDenormalizers()
    {
        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $chainableDenormalizer1Prophecy = $this->prophesize(ChainableFixtureDenormalizerInterface::class);
        $chainableDenormalizer1Prophecy->denormalize(Argument::cetera())->shouldNotBeCalled();
        /** @var ChainableFixtureDenormalizerInterface $chainableDenormalizer1 */
        $chainableDenormalizer1 = $chainableDenormalizer1Prophecy->reveal();

        $chainableDenormalizer2Prophecy = $this->prophesize(ChainableFixtureDenormalizerInterface::class);
        $chainableDenormalizer2Prophecy->denormalize(Argument::cetera())->shouldNotBeCalled();
        /** @var ChainableFixtureDenormalizerInterface $chainableDenormalizer2 */
        $chainableDenormalizer2 = $chainableDenormalizer2Prophecy->reveal();

        $flagParserAwareProphecy = $this->prophesize(FlagParserAwareInterface::class);
        $flagParserAwareProphecy->withParser($flagParser)->shouldBeCalled();
        /** @var FlagParserAwareInterface $flagParserAware */
        $flagParserAware = $flagParserAwareProphecy->reveal();

        $flagParserAwareDenormalizer = new FakeChainableParserAwareDenormalizer($chainableDenormalizer2, $flagParserAware);

        $denormalizer = new FixtureDenormalizerRegistry(
            $flagParser,
            [
                $chainableDenormalizer1,
                $flagParserAwareDenormalizer,
            ]
        );
        $actualDenormalizers = $this->propRefl->getValue($denormalizer);

        $this->assertCount(2, $actualDenormalizers);
        $this->assertSame($chainableDenormalizer1, $actualDenormalizers[0]);
        $this->assertNotSame($flagParserAwareDenormalizer, $actualDenormalizers[1]);
        $this->assertNull($flagParserAwareDenormalizer->parser);
        $this->assertNotNull($actualDenormalizers[1]->parser);
    }
    
    public function testUseTheFirstSuitableDenormalizer()
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn('dummy');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $builtFixtures = new FixtureBag();
        $className = 'Nelmio\Entity\User';
        $reference = 'user0';
        $specs = ['username' => '<name()>'];
        $flags = new FlagBag('');
        $expected = (new FixtureBag())->with($fixture);

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $chainableDenormalizer1Prophecy = $this->prophesize(ChainableFixtureDenormalizerInterface::class);
        $chainableDenormalizer1Prophecy->canDenormalize($reference)->willReturn(false);
        /** @var ChainableFixtureDenormalizerInterface $chainableDenormalizer1 */
        $chainableDenormalizer1 = $chainableDenormalizer1Prophecy->reveal();
        
        $chainableDenormalizer2Prophecy = $this->prophesize(ChainableFixtureDenormalizerInterface::class);
        $chainableDenormalizer2Prophecy->canDenormalize($reference)->willReturn(true);
        $chainableDenormalizer2Prophecy->denormalize($builtFixtures, $className, $reference, $specs, $flags)->willReturn($expected);
        /** @var ChainableFixtureDenormalizerInterface $chainableDenormalizer2 */
        $chainableDenormalizer2 = $chainableDenormalizer2Prophecy->reveal();

        $chainableDenormalizer3Prophecy = $this->prophesize(ChainableFixtureDenormalizerInterface::class);
        $chainableDenormalizer3Prophecy->canDenormalize(Argument::any())->shouldNotBeCalled();
        /** @var ChainableFixtureDenormalizerInterface $chainableDenormalizer3 */
        $chainableDenormalizer3 = $chainableDenormalizer3Prophecy->reveal();

        $denormalizer = new FixtureDenormalizerRegistry(
            $flagParser,
            [
                $chainableDenormalizer1,
                $chainableDenormalizer2,
                $chainableDenormalizer3,
            ]
        );
        $actual = $denormalizer->denormalize($builtFixtures, $className, $reference, $specs, $flags);

        $this->assertSame($expected, $actual);
        $chainableDenormalizer1Prophecy->canDenormalize(Argument::any())->shouldHaveBeenCalledTimes(1);
        $chainableDenormalizer2Prophecy->canDenormalize(Argument::any())->shouldHaveBeenCalledTimes(1);
        $chainableDenormalizer2Prophecy->denormalize(Argument::cetera())->shouldHaveBeenCalledTimes(1);
    }

    /**
     * @expectedException \Nelmio\Alice\Exception\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerNotFoundException
     * @expectedExceptionMessage No suitable fixture denormalizer found to handle the fixture with the reference "user0".
     */
    public function testThrowExceptionIfNotSuitableDenormalizer()
    {
        $builtFixtures = new FixtureBag();
        $className = 'Nelmio\Entity\User';
        $reference = 'user0';
        $specs = ['username' => '<name()>'];
        $flags = new FlagBag('');

        $flagParserProphecy = $this->prophesize(FlagParserInterface::class);
        $flagParserProphecy->parse(Argument::any())->shouldNotBeCalled();
        /** @var FlagParserInterface $flagParser */
        $flagParser = $flagParserProphecy->reveal();

        $denormalizer = new FixtureDenormalizerRegistry($flagParser, []);
        $denormalizer->denormalize($builtFixtures, $className, $reference, $specs, $flags);
    }
}
