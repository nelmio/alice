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

class Dummy
{
    private $title;
    private $counter = 0;

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function addFoo()
    {
        $this->counter++;
    }

    public static function create($title, $counter): self
    {
        $obj = new self();
        $obj->title = $title;
        $obj->counter = $counter;

        return $obj;
    }
}
