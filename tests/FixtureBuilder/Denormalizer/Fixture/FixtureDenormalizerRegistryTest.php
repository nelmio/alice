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

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\DummyChainableParserAwareDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\FakeChainableDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable\FakeChainableDenormalizerAwareDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParser\FakeFlagParser;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;
use Nelmio\Alice\FixtureInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use TypeError;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerRegistry
 */
class FixtureDenormalizerRegistryTest extends TestCase
{
    /**
     * @var \ReflectionProperty
     */
    private $propRefl;

    /**
     * @inheritdoc
     */
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
        $flagParser = new FakeFlagParser();

        try {
            new FixtureDenormalizerRegistry($flagParser, [new \stdClass()]);
            $this->fail('Expected exception to be thrown.');
        } catch (TypeError $error) {
            $this->assertEquals(
                'Expected denormalizer 0 to be a "'.ChainableFixtureDenormalizerInterface::class.'". Got '
                .'"stdClass" instead.',
                $error->getMessage()
            );
        }

        try {
            new FixtureDenormalizerRegistry($flagParser, [1]);
            $this->fail('Expected exception to be thrown.');
        } catch (TypeError $error) {
            $this->assertEquals(
                'Expected denormalizer 0 to be a "'.ChainableFixtureDenormalizerInterface::class.'". Got '
                .'"integer" instead.',
                $error->getMessage()
            );
        }
    }
    
    public function testInjectsParserInParserAwareDenormalizersAndItselfInDenormalizerAwareDenormalizers()
    {
        $flagParser = new FakeFlagParser();
        $chainableDenormalizer1 = new FakeChainableDenormalizer();
        $chainableDenormalizer2 = new FakeChainableDenormalizer();

        $flagParserAwareProphecy = $this->prophesize(FlagParserAwareInterface::class);
        $flagParserAwareProphecy->withFlagParser($flagParser)->shouldBeCalled();
        /** @var FlagParserAwareInterface $flagParserAware */
        $flagParserAware = $flagParserAwareProphecy->reveal();

        $flagParserAwareDenormalizer = new DummyChainableParserAwareDenormalizer($chainableDenormalizer2, $flagParserAware);
        $denormalizerAwareDenormalizer = new FakeChainableDenormalizerAwareDenormalizer();

        $denormalizer = new FixtureDenormalizerRegistry(
            $flagParser,
            [
                $chainableDenormalizer1,
                $flagParserAwareDenormalizer,
                $denormalizerAwareDenormalizer,
            ]
        );
        $actualDenormalizers = $this->propRefl->getValue($denormalizer);

        $this->assertCount(3, $actualDenormalizers);
        $this->assertSame($chainableDenormalizer1, $actualDenormalizers[0]);
        $this->assertNotSame($flagParserAwareDenormalizer, $actualDenormalizers[1]);
        $this->assertNull($flagParserAwareDenormalizer->parser);
        $this->assertNotNull($actualDenormalizers[1]->parser);
        $this->assertSame($denormalizer, $denormalizerAwareDenormalizer->denormalizer);
    }
    
    public function testUsesTheFirstSuitableDenormalizer()
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn('dummy');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $builtFixtures = new FixtureBag();
        $className = 'Nelmio\Alice\Entity\User';
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
     * @expectedException \Nelmio\Alice\Throwable\Exception\FixtureBuilder\Denormalizer\DenormalizerNotFoundException
     * @expectedExceptionMessage No suitable fixture denormalizer found to handle the fixture with the reference "user0".
     */
    public function testThrowsExceptionIfNotSuitableDenormalizer()
    {
        $builtFixtures = new FixtureBag();
        $className = 'Nelmio\Alice\Entity\User';
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
