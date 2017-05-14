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

class DummyWithStaticFunction
{
    private $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public static function setTitle(self $instance, string $title)
    {
        $instance->title = $title;
    }
}
