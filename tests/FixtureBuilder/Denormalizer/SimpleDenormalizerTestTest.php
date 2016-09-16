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

use Nelmio\Alice\Definition\Fixture\DummyFixture;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\BareFixtureSet;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FakeFixtureBagDenormalizer;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Parameter\FakeParameterBagDenormalizer;
use Nelmio\Alice\FixtureBuilder\DenormalizerInterface;
use Nelmio\Alice\ParameterBag;
use Prophecy\Argument;

/**
 * @covers \Nelmio\Alice\FixtureBuilder\Denormalizer\SimpleDenormalizer
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
        clone new SimpleDenormalizer(new FakeParameterBagDenormalizer(), new FakeFixtureBagDenormalizer());
    }

    public function testReturnsADenormalizedSet()
    {
        $data = [
            'parameters' => [
                'foo' => 'bar',
            ],
            'Nelmio\Alice\Entity\User' => [
                'user1' => [],
            ],
            'Nelmio\Alice\Entity\Group' => [
                'group1' => [],
            ],
        ];
        $fixturesData = [
            'Nelmio\Alice\Entity\User' => [
                'user1' => [],
            ],
            'Nelmio\Alice\Entity\Group' => [
                'group1' => [],
            ],
        ];

        $parameterDenormalizerProphecy = $this->prophesize(ParameterBagDenormalizerInterface::class);
        $parameterDenormalizerProphecy
            ->denormalize($data)
            ->willReturn(
                $parameters = new ParameterBag(['foo' => 'bar'])
            )
        ;
        /** @var ParameterBagDenormalizerInterface $parameterDenormalizer */
        $parameterDenormalizer = $parameterDenormalizerProphecy->reveal();

        $fixturesDenormalizerProphecy = $this->prophesize(FixtureBagDenormalizerInterface::class);
        $fixturesDenormalizerProphecy
            ->denormalize($fixturesData)
            ->willReturn(
                $fixtures = (new FixtureBag())->with(new DummyFixture('foo'))
            )
        ;
        /** @var FixtureBagDenormalizerInterface $fixturesDenormalizer */
        $fixturesDenormalizer = $fixturesDenormalizerProphecy->reveal();

        $expected = new BareFixtureSet($parameters, $fixtures);

        $denormalizer = new SimpleDenormalizer($parameterDenormalizer, $fixturesDenormalizer);
        $actual = $denormalizer->denormalize($data);

        $this->assertEquals($expected, $actual);

        $parameterDenormalizerProphecy->denormalize(Argument::any())->shouldHaveBeenCalledTimes(1);
        $fixturesDenormalizerProphecy->denormalize(Argument::any())->shouldHaveBeenCalledTimes(1);
    }
}
