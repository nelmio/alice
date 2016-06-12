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

use Nelmio\Alice\Definition\Flag\UniqueFlag;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\Property;
use Nelmio\Alice\Definition\Value\UniqueValue;
use Nelmio\Alice\FixtureInterface;

/**
 * @covers Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\SpecificationBagDenormalizer\PropertyDenormalizer
 */
class PropertyDenormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PropertyDenormalizer
     */
    private $denormalizer;

    public function setUp()
    {
        $this->denormalizer = new PropertyDenormalizer();
    }

    public function testDenormalize()
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->shouldNotBeCalled();
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $name = 'username';
        $value = '<username()>';
        $flags = new FlagBag('');

        $expected = new Property($name, $value);

        $actual = $this->denormalizer->denormalize($fixture, $name, $value, $flags);
        $this->assertEquals($expected, $actual);
    }

    public function testDenormalizeWithUniqueFlag()
    {
        $fixtureProphecy = $this->prophesize(FixtureInterface::class);
        $fixtureProphecy->getId()->willReturn('dummy');
        /** @var FixtureInterface $fixture */
        $fixture = $fixtureProphecy->reveal();

        $name = 'username';
        $value = '<username()>';
        $flags = (new FlagBag(''))->with(new UniqueFlag());

        $expected = new Property($name, $value);

        $result = $this->denormalizer->denormalize($fixture, $name, $value, $flags);
        $this->assertEquals('username', $expected->getName());
        /** @var UniqueValue $uniqueValue */
        $uniqueValue = $result->getValue();
        $this->assertInstanceOf(UniqueValue::class, $uniqueValue);
        $this->assertEquals(1, preg_match('/^dummy.+$/', $uniqueValue->getId()));
    }
}
