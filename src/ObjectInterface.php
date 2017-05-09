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
     * @return string
     *
     * @example
     *  'user0'
     */
    public function getId(): string;

    /**
     * @return object
     */
    public function getInstance();

    /**
     * @param object $newInstance
     *
     * @return static
     */
    public function withInstance($newInstance);
}
