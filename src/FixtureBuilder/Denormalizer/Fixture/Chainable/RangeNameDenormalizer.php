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

use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\RangeName;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Nelmio\Alice\FixtureInterface;

final class RangeNameDenormalizer extends AbstractChainableDenormalizer
{
    /**
     * @var string Unique token
     */
    private $token;

    public function __construct(FixtureDenormalizerInterface $denormalizer = null)
    {
        parent::__construct($denormalizer);

        $this->token = uniqid(__CLASS__);
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
        string $fixtureId,
        array $specs,
        FlagBag $flags): FixtureBag
    {
        /**
         * @var FixtureInterface $tempFixture
         * @var FixtureBag       $builtFixtures
         */
        list($tempFixture, $builtFixtures) = $this->denormalizeTemporaryFixture(
            $builtFixtures,
            $className,
            $specs,
            $flags
        );

        $range = $this->buildRange($fixtureId);
        for ($currentIndex = $range->getFrom(); $currentIndex <= $range->getTo(); $currentIndex++) {
            $fixtureId = str_replace($this->token, $currentIndex, $range->getName());

            $builtFixtures = $builtFixtures->with(
                new SimpleFixture(
                    $fixtureId,
                    $tempFixture->getClassName(),
                    $tempFixture->getSpecs()
                )
            );
        }

        return $builtFixtures;
    }
    /**
     * @param string $name
     *
     * @return RangeName
     *
     * @example
     *  'user{1..10}' => new RangeName('user', 1, 10)
     */
    private function buildRange(string $name): RangeName
    {
        $matches = [];
        if (false === $this->canDenormalize($name, $matches)) {
            throw new \LogicException(
                sprintf(
                    'As a chainable denormalizer, "%s" should be called only if "::canDenormalize() returns true. Got false instead.',
                    __METHOD__
                )
            );
        }
        $reference = str_replace(sprintf('{%s}', $matches['range']), $this->token, $name);

        return new RangeName($reference, $matches['from'], $matches['to']);
    }
}
