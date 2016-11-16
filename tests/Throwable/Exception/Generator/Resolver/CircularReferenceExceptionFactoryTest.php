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

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\CircularReferenceExceptionFactory
 */
class CircularReferenceExceptionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = CircularReferenceExceptionFactory::createForParameter('foo', ['bar' => 1, 'baz' => 0]);

        $this->assertEquals(
            'Circular reference detected for the parameter "foo" while resolving ["bar", "baz"].',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}

