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

class MutableUser implements UserInterface
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
    private $createdAt;

    /**
     * @var \DateTimeInterface
     */
    private $updatedAt;

    private $owner;

    private $members;

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

    public function setUsername(string $username)
    {
        $this->username = $username;
    }

    public function getFullname(): string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname)
    {
        $this->fullname = $fullname;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    public function getOwner(): \stdClass
    {
        return $this->owner;
    }

    public function setOwner(ImmutableUser $owner)
    {
        $this->owner = $owner;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function addMember(ImmutableUser $member)
    {
        $this->members[] = $member;
    }

    public function setMembers(array $members)
    {
        $this->members = $members;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getFavoriteNumber(): int
    {
        return $this->favoriteNumber;
    }

    public function setFavoriteNumber(int $favoriteNumber)
    {
        $this->favoriteNumber = $favoriteNumber;
    }
}
