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

final class Address
{
    private $country;
    private $city;

    public function getCountry()
    {
        return $this->country;
    }

    public function setCountry(string $country)
    {
        $this->country = $country;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city)
    {
        $this->city = $city;
    }
}
