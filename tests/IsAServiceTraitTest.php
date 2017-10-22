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

namespace Nelmio\Alice;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Throwable;

/**
 * @covers \Nelmio\Alice\IsAServiceTrait
 */
class IsAServiceTraitTest extends TestCase
{
    public function testThrowsAnExceptionWhenTryingToCloneInstance()
    {
        try {
            clone new NotClonableDummy();
            $this->fail('Expected exception to be thrown.');
        } catch (Throwable $exception) {
            $this->assertEquals(
                'Call to private Nelmio\Alice\NotClonableDummy::__clone() from context '
                .'\'Nelmio\Alice\IsAServiceTraitTest\'',
                $exception->getMessage()
            );
            $this->assertEquals(0, $exception->getCode());
            $this->assertNull($exception->getPrevious());
        }

        $dummyRefl = new ReflectionClass(NotClonableDummy::class);

        $this->assertFalse($dummyRefl->isCloneable());
    }
}
