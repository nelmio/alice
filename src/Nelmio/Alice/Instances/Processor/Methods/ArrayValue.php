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

use Nelmio\Alice\Instances\Processor\Processor;
use Nelmio\Alice\Instances\Processor\ProcessableInterface;

class ArrayValue implements MethodInterface
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * sets the processor to handle recursive calls.
     *
     * @param Processor
     */
    public function setProcessor(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * {@inheritdoc}
     */
    public function canProcess(ProcessableInterface $processable)
    {
        return is_array($processable->getValue());
    }

    /**
     * {@inheritdoc}
     */
    public function process(ProcessableInterface $processable, array $variables)
    {
        $values = $processable->getValue();
        foreach ($values as $key => $value) {
            $values[$key] = $this->processor->process($value, $variables);
        }

        return $values;
    }
}
