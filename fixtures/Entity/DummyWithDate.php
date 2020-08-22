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

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

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

    public function setImmutableDateTime(DateTimeImmutable $dateTime): void
    {
        $this->data['immutable'] = $dateTime;
    }

    public function setMutableDateTime(DateTime $datedateTime): void
    {
        $this->data['mutable'] = $datedateTime;
    }

    public function setDateTimeInterface(DateTimeInterface $dateTime): void
    {
        $this->data['interface'] = $dateTime;
    }
}
