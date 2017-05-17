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

namespace Nelmio\Alice\Entity\Caller;

use Closure;

class StaticService
{
    public static function setTitle(DummyWithStaticFunction $instance, string $title)
    {
        Closure::bind(
            function (DummyWithStaticFunction $dummy) use ($title) {
                $dummy->title = $title;
            },
            null,
            DummyWithStaticFunction::class
        )($instance);
    }
}
