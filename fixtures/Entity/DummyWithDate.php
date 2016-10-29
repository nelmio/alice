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

namespace Nelmio\Alice\Entity;

class DummyWithDate
{
    /**
     * @var array
     */
    private $data;

    public function __construct($data = [])
    {
        $this->data = $data;
    }

    public function setImmutableDateTime(\DateTimeImmutable $dateTime)
    {
        $this->data['immutable'] = $dateTime;
    }

    public function setMutableDateTime(\DateTime $datedateTime)
    {
        $this->data['mutable'] = $datedateTime;
    }

    public function setDateTimeInterface(\DateTimeInterface $dateTime)
    {
        $this->data['interface'] = $dateTime;
    }
}
