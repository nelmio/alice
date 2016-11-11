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

namespace Nelmio\Alice\scenario3;

class ImmutableUser implements UserInterface
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
     * @var \DateTimeInterface
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

    public function __construct(
        string $username,
        string $fullname,
        \DateTimeInterface $birthDate,
        string $email,
        int $favoriteNumber
    ) {
        $this->username = $username;
        $this->fullname = $fullname;
        $this->birthDate = $birthDate;
        $this->email = $email;
        $this->favoriteNumber = $favoriteNumber;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getFullname(): string
    {
        return $this->fullname;
    }

    public function getBirthDate(): \DateTimeInterface
    {
        return $this->birthDate;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFavoriteNumber(): int
    {
        return $this->favoriteNumber;
    }
}
