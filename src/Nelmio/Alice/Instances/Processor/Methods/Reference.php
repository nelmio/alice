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

    /**
     * Sets the object collection to handle referential calls.
     *
     * @param Collection $objects
     */
    public function setObjects(Collection $objects)
    {
        $this->objects = $objects;
    }

    /**
     * {@inheritDoc}
     */
    public function canProcess(ProcessableInterface $processable)
    {
        $multiPart = '(?:(?<multi>\d+)x +)';
        $referencePart = '@(?<reference>\{?[^\{\}\-\>\']+\}?)';
        $sequencePart = '(?<sequence>\{(?<from>\d+)\.\.(?<to>\d+)\})';
        $propertyPart = '(?:\->(?<property>[a-z0-9_-]+))';

        $regex = "/^\\'?{$multiPart}?{$referencePart}{$sequencePart}?{$propertyPart}?\\'?$/i";

        return
            is_string($processable->getValue())
            && $processable->valueMatches($regex)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ProcessableInterface $processable, array $variables)
    {
        $multi = ('' !== $processable->getMatch('multi')) ? (int) $processable->getMatch('multi') : null;
        $property = null !== ($processable->getMatch('property')) ? $processable->getMatch('property') : null;
        $sequence = null !== ($processable->getMatch('sequence')) ? $processable->getMatch('sequence') : null;
        $reference = null !== $processable->getMatch('escaped_reference')
            ? $processable->getMatch('escaped_reference')
            : $processable->getMatch('reference')
        ;

        if (strpos($reference, '*')) {
            return $this->objects->random($reference, $multi, $property);
        }

        if ($sequence && $reference = $processable->getMatch('reference')) {
            $from = $processable->getMatch('from');
            $to = $processable->getMatch('to');

            $set = [];
            foreach (range($from, $to) as $id) {
                $set[] = $this->objects->get($reference . $id);
            }

            return $set;
        }

        if (null !== $multi) {
            throw new \UnexpectedValueException('To use multiple references you must use a mask like "'.$processable->getMatch('multi').'x @user*", otherwise you would always get only one item.');
        }

        return $this->objects->find($reference, $property);
    }
}
