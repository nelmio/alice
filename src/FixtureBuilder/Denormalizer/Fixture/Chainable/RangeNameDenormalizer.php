<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\RangeName;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\ChainableFixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerAwareInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;

final class RangeNameDenormalizer implements ChainableFixtureDenormalizerInterface, FixtureDenormalizerAwareInterface
{
    /**
     * @var FixtureDenormalizerInterface|null
     */
    private $denormalizer;

    /**
     * @var string Unique token
     */
    private $token;

    public function __construct()
    {
        $this->token = uniqid(__CLASS__);
    }

    /**
     * @inheritdoc
     */
    public function with(FixtureDenormalizerInterface $denormalizer): self
    {
        $clone = clone $this;
        $clone->denormalizer = $denormalizer;

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function canDenormalize(string $name, array &$matches = []): bool
    {
        return 1 === preg_match('/.+\{(?<range>(?<from>[0-9]+)(?:\.{2})(?<to>[0-9]+))\}/', $name, $matches);
    }

    /**
     * @inheritdoc
     */
    public function denormalize(
        FixtureBag $builtFixtures,
        string $className,
        string $reference,
        array $specs,
        FlagBag $flags): FixtureBag
    {
        if (null === $this->denormalizer) {
            throw new \BadMethodCallException(
                sprintf(
                    'Expected method "%s" to be called only if it has a denormalizer.',
                    __METHOD__
                )
            );
        }

        $range = $this->getRanges($reference);
        for ($currentIndex = $range->getFrom(); $currentIndex <= $range->getTo(); $currentIndex++) {
            $reference = str_replace($this->token, $currentIndex, $range->getName());

            $builtFixtures = $builtFixtures->mergeWith(
                $this->denormalizer->denormalize($builtFixtures, $className, $reference, $specs, $flags)
            );
        }

        return $builtFixtures;
    }
    /**
     * @param string $name
     *
     * @throws \BadMethodCallException
     *
     * @return RangeName
     */
    private function getRanges(string $name): RangeName
    {
        $matches = [];
        if (false === $this->canDenormalize($name, $matches)) {
            throw new \BadMethodCallException(
                sprintf(
                    'As a chainable denormalizer, "%s" should be called only if "::canDenormalize() returns true. Got false instead.',
                    __METHOD__
                )
            );
        }
        $reference = str_replace(sprintf('{%s}', $matches['range']), $this->token, $name);

        return new RangeName($reference, $matches['from'], $matches['to']);
    }

    public function __clone()
    {
        if (null !== $this->denormalizer) {
            $this->denormalizer = clone $this->denormalizer;
        }
    }
}
