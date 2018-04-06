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

namespace Nelmio\Alice;

class UserDetail
{
    /**
     * @var string|null
     */
    private $email;

    /**
     * @var User
     */
    private $user;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): UserDetail
    {
        $this->email = $email;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): UserDetail
    {
        $this->user = $user;

        return $this;
    }
}
