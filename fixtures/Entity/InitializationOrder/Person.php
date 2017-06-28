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

namespace Nelmio\Alice\Entity\InitializationOrder;

final class Person
{
    private $city;
    private $country;

    public static function createWithAddress($address)
    {
        $instance = new self();

        $instance->city = $address->getCity();
        $instance->country = $address->getCountry();

        return $instance;
    }
}
