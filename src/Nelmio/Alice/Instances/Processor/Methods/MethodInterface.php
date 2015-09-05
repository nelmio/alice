<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Processor\Methods;

use Nelmio\Alice\Instances\Processor\ProcessableInterface;

interface MethodInterface
{
    /**
     * returns true if this method can process the given value persister
     *
     * @param ProcessableInterface
     * @return boolean
     */
    public function canProcess(ProcessableInterface $processable);

    /**
     * returns the processed value
     *
     * @param ProcessableInterface
     * @param array
     * @return mixed
     */
    public function process(ProcessableInterface $processable, array $variables);
}
