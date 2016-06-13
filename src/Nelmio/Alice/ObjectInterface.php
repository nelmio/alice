<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

/**
 * An object is an instance with a reference.
 */
interface ObjectInterface
{
    /**
     * @return string
     *
     * @example
     *  'user0'
     */
    public function getReference(): string;

    /**
     * @return \object
     */
    public function getInstance();
}
