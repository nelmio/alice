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

class Conditional implements MethodInterface
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * sets the processor to handle recursive calls
     *
     * @param Processor
     */
    public function setProcessor(Processor $processor)
    {
        $this->processor = $processor;
    }

    /**
     * {@inheritDoc}
     */
    public function canProcess(ProcessableInterface $processable)
    {
        return is_string($processable->getValue()) && $processable->valueMatches('{^(?<threshold>[0-9.]+%?)\? (?<trueValue>.+?)(?: : (?<falseValue>.+?))?$}');
    }

    /**
     * {@inheritDoc}
     */
    public function process(ProcessableInterface $processable, array $variables)
    {
        $trueValue = $this->processor->process($processable->getMatch('trueValue'), $variables);

        if ($this->shouldReturnTrue($processable)) {
            return $trueValue;
        } elseif (!is_null($processable->getMatch('falseValue')) && '' !== $processable->getMatch('falseValue')) {
            return $this->processor->process($processable->getMatch('falseValue'), $variables);
        } else {
            return is_array($trueValue) ? [] : null;
        }
    }

    /**
     * compares the threshold to a randomly generated value to determine whether
     *
     * @param ProcessableInterface $processable
     * @return
     */
    private function shouldReturnTrue(ProcessableInterface $processable)
    {
        $threshold = $processable->getMatch('threshold');
        if (substr($threshold, -1) === '%') {
            $threshold = substr($threshold, 0, -1) / 100;
        }

        return $threshold > 0 && (mt_rand(0, 100) / 100) <= $threshold;
    }
}
