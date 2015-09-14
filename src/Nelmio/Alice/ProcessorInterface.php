<?php

/**
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice;

interface ProcessorInterface
{
    /**
     * Processes an object before it is persisted to DB
     *
     * @param object $object instance to process
     */
    public function preProcess($object);

    /**
     * Processes an object after it is persisted to DB
     *
     * @param object $object instance to process
     */
    public function postProcess($object);
}
