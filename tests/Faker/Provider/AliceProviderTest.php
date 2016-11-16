<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace Nelmio\Alice\Faker\Provider;

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Throwable\Exception\NoValueForCurrentException;

/**
 * @covers \Nelmio\Alice\Faker\Provider\AliceProvider
 */
class AliceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testIdentityReturnsTheValueUnchanged()
    {
        $value = $expected ='foo';
        $actual = AliceProvider::identity($value);

        $this->assertEquals($expected, $actual);
    }

    public function testCurrentReturnsFixtureCurrentValue()
    {
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create(), $expected = 'foo');
        $expected = 'foo';

        $actual = AliceProvider::current($fixture);

        $this->assertEquals($expected, $actual);
    }

    public function testCurrentThrowsAnExceptionIfFixtureHasNoCurrentValue()
    {
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create());
        try {
            AliceProvider::current($fixture);
            $this->fail('Expected exception to be thrown.');
        } catch (NoValueForCurrentException $exception) {
            $this->assertEquals(
                'No value for \'<current()>\' found for the fixture "dummy".',
                $exception->getMessage()
            );
            $this->assertEquals(0, $exception->getCode());
            $this->assertNull($exception->getPrevious());
        }
    }

    /**
     * @dataProvider provideValuesToCast
     */
    public function testCastReturnsCastedValue(string $type, $value, $expected)
    {
        $actual = AliceProvider::cast($type, $value);

        $this->assertEquals($expected, $actual);
    }

    public function provideValuesToCast()
    {
        yield '"-1" to int' => [
            'int',
            '-1',
            -1,
        ];
    }
}
