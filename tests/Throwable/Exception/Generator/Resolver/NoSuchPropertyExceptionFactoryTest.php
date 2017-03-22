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

namespace Nelmio\Alice\Throwable\Exception\Generator\Resolver;

use PHPUnit\Framework\TestCase;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\SpecificationBagFactory;
use Nelmio\Alice\Definition\Value\FakeValue;
use Nelmio\Alice\Definition\Value\FixturePropertyValue;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\NoSuchPropertyExceptionFactory
 */
class NoSuchPropertyExceptionFactoryTest extends TestCase
{
    public function testTestCreateForFixture()
    {
        $fixture = new SimpleFixture('dummy', 'Dummy', SpecificationBagFactory::create());
        $property = new FixturePropertyValue(new FakeValue(), 'foo');

        $exception = NoSuchPropertyExceptionFactory::createForFixture($fixture, $property);

        $this->assertEquals(
            'Could not find the property "foo" of the object "dummy" (class: Dummy).',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());


        $code = 500;
        $previous = new \Error();

        $exception = NoSuchPropertyExceptionFactory::createForFixture($fixture, $property, $code, $previous);

        $this->assertEquals(
            'Could not find the property "foo" of the object "dummy" (class: Dummy).',
            $exception->getMessage()
        );
        $this->assertEquals($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }
}

