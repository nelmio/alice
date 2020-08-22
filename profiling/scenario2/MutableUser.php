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

namespace Nelmio\Alice\scenario2;

use DateTimeInterface;

class MutableUser
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $fullname;

    /**
     * @var DateTimeInterface
     */
    private $birthDate;

    /**
     * @var string
     */
    private $email;

    /**
     * @var int
     */
    private $favoriteNumber;

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getFullname(): string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): void
    {
        $this->fullname = $fullname;
    }

    public function getBirthDate(): DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(DateTimeInterface $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getFavoriteNumber(): int
    {
        return $this->favoriteNumber;
    }

    public function setFavoriteNumber(int $favoriteNumber): void
    {
        $this->favoriteNumber = $favoriteNumber;
    }
}
