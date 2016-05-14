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
     * Returns true if this method can process the given value.
     *
     * @param ProcessableInterface $processable
     *
     * @return bool
     */
    public function canProcess(ProcessableInterface $processable);

    /**
     * Returns the processed value.
     *
     * @param ProcessableInterface $processable
     * @param array
     * 
     * @return mixed
     */
    public function process(ProcessableInterface $processable, array $variables);
}
