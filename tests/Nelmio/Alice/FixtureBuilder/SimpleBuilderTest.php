<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder;

use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FakeDenormalizer;
use Nelmio\Alice\FixtureBuilder\Parser\FakeParser;
use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\SimpleBuilder
 */
class SimpleBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testIsAFixtureBuilder()
    {
        $this->assertTrue(is_a(SimpleBuilder::class, FixtureBuilderInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $builder = new SimpleBuilder(new FakeParser(), new FakeDenormalizer());
        clone $builder;
    }

    public function testBuildSet()
    {
        $file = 'dummy.yml';
        $injectedParameters = ['foo' => 'bar'];
        $injectedObjects = ['std' => new \stdClass()];

        $parseData = ['dummy' => new \stdClass()];
        $loadedParameters = new ParameterBag(['rab' => 'oof']);
        $loadedFixtures = new FixtureBag();
        $set = new BareFixtureSet($loadedParameters, $loadedFixtures);

        $expected = new FixtureSet($loadedParameters, new ParameterBag($injectedParameters), $loadedFixtures, new ObjectBag($injectedObjects));

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse($file)->willReturn($parseData);
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $denormalizerProphecy->denormalize($parseData)->willReturn($set);
        /** @var DenormalizerInterface $denormalizer */
        $denormalizer = $denormalizerProphecy->reveal();

        $builder = new SimpleBuilder($parser, $denormalizer);
        $actual = $builder->build($file, $injectedParameters, $injectedObjects);

        $this->assertEquals($expected, $actual);

        $parserProphecy->parse(Argument::any())->shouldHaveBeenCalledTimes(1);
        $denormalizerProphecy->denormalize(Argument::any())->shouldHaveBeenCalledTimes(1);
    }

    public function testBuildSetWithoutInjectingParametersOrObjects()
    {
        $file = 'dummy.yml';

        $parseData = ['dummy' => new \stdClass()];
        $loadedParameters = new ParameterBag(['rab' => 'oof']);
        $loadedFixtures = new FixtureBag();
        $set = new BareFixtureSet($loadedParameters, $loadedFixtures);

        $expected = new FixtureSet($loadedParameters, new ParameterBag(), $loadedFixtures, new ObjectBag());

        $parserProphecy = $this->prophesize(ParserInterface::class);
        $parserProphecy->parse($file)->willReturn($parseData);
        /** @var ParserInterface $parser */
        $parser = $parserProphecy->reveal();

        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $denormalizerProphecy->denormalize($parseData)->willReturn($set);
        /** @var DenormalizerInterface $denormalizer */
        $denormalizer = $denormalizerProphecy->reveal();

        $builder = new SimpleBuilder($parser, $denormalizer);
        $actual = $builder->build($file);

        $this->assertEquals($expected, $actual);
    }
}
