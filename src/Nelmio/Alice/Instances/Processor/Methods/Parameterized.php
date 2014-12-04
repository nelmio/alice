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

class Parameterized implements MethodInterface
{
    /**
     * @var Processor
     */
    private $processor;

    /**
     * sets the processor to handle recursive calls
     *
     * @param Processor $processor
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
        return is_string($processable->getValue());
    }

    /**
     * {@inheritDoc}
     */
    public function process(ProcessableInterface $processable, array $variables)
    {
        $value = $processable->getValue();
        $parameterBag = $this->processor->getParameterBag();

        return preg_replace_callback('#<\{([a-z0-9_\.-]+)\}>#i', function ($matches) use ($parameterBag) {
            $key = $matches[1];
            if (!$parameterBag->has($key)) {
                throw new \UnexpectedValueException(sprintf(
                    'Parameter "%s" was not found',
                    $key
                ));
            }

            return $parameterBag->get($key);
        }, $value);
    }

}
