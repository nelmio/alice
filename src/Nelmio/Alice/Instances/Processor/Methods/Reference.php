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

use Nelmio\Alice\Instances\Collection;
use Nelmio\Alice\Instances\Processor\ProcessableInterface;

class Reference implements MethodInterface
{
    /**
     * @var Collection
     */
    protected $objects;

    public function __construct(Collection $objects)
    {
        $this->objects = $objects;
    }

    /**
     * {@inheritDoc}
     */
    public function canProcess(ProcessableInterface $processable)
    {
        return is_string($processable->getValue()) && $processable->valueMatches('{^(?:(?<multi>\d+)x )?@(?<reference>[a-z0-9_.*-]+)(?:\->(?<property>[a-z0-9_-]+))?$}i');
    }

    /**
     * {@inheritDoc}
     */
    public function process(ProcessableInterface $processable, array $variables)
    {
        $multi = ('' !== $processable->getMatch('multi')) ? $processable->getMatch('multi') : null;
        $property = !is_null($processable->getMatch('property')) ? $processable->getMatch('property') : null;

        if (strpos($processable->getMatch('reference'), '*')) {
            return $this->objects->random($processable->getMatch('reference'), $multi, $property);
        } else {
            if (null !== $multi) {
                throw new \UnexpectedValueException('To use multiple references you must use a mask like "'.$processable->getMatch('multi').'x @user*", otherwise you would always get only one item.');
            }

            return $this->objects->find($processable->getMatch('reference'), $property);
        }
    }

}
