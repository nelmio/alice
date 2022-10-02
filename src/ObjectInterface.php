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

/**
 * An object is an instance (the real object) with a reference (fixture describing the instance).
 */
interface ObjectInterface
{
    /**
     * @example
     *  'user0'
     */
    public function getId(): string;

    public function getInstance(): object;

    public function withInstance(object $newInstance): static;
}
