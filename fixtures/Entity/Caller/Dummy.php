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
    private $related;

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function addFoo()
    {
        $this->counter++;
    }

    public function setRelatedDummy(self $related)
    {
        $this->related = $related;
    }

    public static function create($title, $counter, self $related = null): self
    {
        $obj = new self();
        $obj->title = $title;
        $obj->counter = $counter;
        $obj->related = $related;

        return $obj;
    }
}
