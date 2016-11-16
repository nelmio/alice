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

namespace Nelmio\Alice\Throwable\Exception;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\FixtureNotFoundExceptionFactory
 */
class FixtureNotFoundExceptionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = FixtureNotFoundExceptionFactory::create('foo');

        $this->assertEquals(
            'Could not find the fixture "foo".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
