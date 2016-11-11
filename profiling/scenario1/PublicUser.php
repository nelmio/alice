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

namespace Nelmio\Alice\scenario1;

class PublicUser
{
    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $fullname;

    /**
     * @var \DateTimeInterface
     */
    public $birthDate;

    /**
     * @var string
     */
    public $email;

    /**
     * @var int
     */
    public $favoriteNumber;
}
