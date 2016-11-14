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

use Nelmio\Alice\Definition\Value\UniqueValue;

/**
 * @covers \Nelmio\Alice\Throwable\Exception\Generator\Resolver\UniqueValueGenerationLimitReachedExceptionFactory
 */
class UniqueValueGenerationLimitReachedExceptionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testTestCreateNewExceptionWithFactory()
    {
        $exception = UniqueValueGenerationLimitReachedExceptionFactory::create(
            new UniqueValue('unique_id', new \stdClass()),
            10
        );

        $this->assertEquals(
            'Could not generate a unique value after 10 attempts for "unique_id".',
            $exception->getMessage()
        );
        $this->assertEquals(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
