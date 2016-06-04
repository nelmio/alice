<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer;

use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\BareFixtureSet;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FakeFixtureBagDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter\FakeParameterBagDenormalizer;
use Nelmio\Alice\FixtureBuilder\DenormalizerInterface;
use Nelmio\Alice\ParameterBag;
use Prophecy\Argument;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\SimpleDenormalizer
 */
class SimpleDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    public function testIsADenormalizer()
    {
        $this->assertTrue(is_a(SimpleDenormalizer::class, DenormalizerInterface::class, true));
    }

    /**
     * @expectedException \DomainException
     */
    public function testIsNotClonable()
    {
        $denormalizer = new SimpleDenormalizer(new FakeParameterBagDenormalizer(), new FakeFixtureBagDenormalizer());
        clone $denormalizer;
    }

    public function testDenormalizerGivenData()
    {
        $data = [
            'parameters' => 'something',
            'Dummy' => new \stdClass(),
        ];
        $expectedParameters = new ParameterBag(['foo' => 'bar']);
        $expectedFixtures = new FixtureBag(['std' => new \stdClass()]);
        $expectedSet = new BareFixtureSet($expectedParameters, $expectedFixtures);

        $parameterDenormalizerProphecy = $this->prophesize(ParameterBagDenormalizerInterface::class);
        $parameterDenormalizerProphecy->denormalize($data)->willReturn($expectedParameters);
        /** @var ParameterBagDenormalizerInterface $parameterDenormalizer */
        $parameterDenormalizer = $parameterDenormalizerProphecy->reveal();

        $fixtureDenormalizerProphecy = $this->prophesize(FixtureBagDenormalizerInterface::class);
        $fixtureDenormalizerProphecy
            ->denormalize([
                // no parameters
                'Dummy' => new \stdClass(),
            ])
            ->willReturn($expectedFixtures)
        ;
        /** @var FixtureBagDenormalizerInterface $fixtureDenormalizer */
        $fixtureDenormalizer = $fixtureDenormalizerProphecy->reveal();

        $denormalizer = new SimpleDenormalizer($parameterDenormalizer, $fixtureDenormalizer);
        $actual = $denormalizer->denormalize($data);

        $this->assertEquals($expectedSet, $actual);

        $parameterDenormalizerProphecy->denormalize(Argument::any())->shouldHaveBeenCalledTimes(1);
        $fixtureDenormalizerProphecy->denormalize(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
