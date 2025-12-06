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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Throwable;

/**
 * @internal
 */
#[CoversClass(IsAServiceTrait::class)]
final class IsAServiceTraitTest extends TestCase
{
    public function testThrowsAnExceptionWhenTryingToCloneInstance(): void
    {
        try {
            clone new NotClonableDummy();
            self::fail('Expected exception to be thrown.');
        } catch (Throwable $exception) {
            self::assertEquals(0, $exception->getCode());
            self::assertNull($exception->getPrevious());

            self::assertEquals(
                'Call to private Nelmio\Alice\NotClonableDummy::__clone() from scope '
                .'Nelmio\Alice\IsAServiceTraitTest',
                $exception->getMessage(),
            );
        }

        $dummyRefl = new ReflectionClass(NotClonableDummy::class);

        self::assertFalse($dummyRefl->isCloneable());
    }
}
