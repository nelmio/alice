<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\Chainable;

use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\Definition\RangeName;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\LogicExceptionFactory;

final class NullRangeNameDenormalizer implements CollectionDenormalizer
{
    use IsAServiceTrait;

    /** @private */
    const REGEX = '/.+\{(?<range>(?<from>[0-9]+)(?:\.{2})(?<to>[0-9]+))\}/';

    /**
     * @var string Unique token
     */
    private $token;

    public function __construct()
    {
        $this->token = uniqid(__CLASS__, true);
    }

    /**
     * @inheritdoc
     */
    public function canDenormalize(string $name, array &$matches = []): bool
    {
        return 1 === preg_match(self::REGEX, $name, $matches);
    }

    /**
     * @inheritdoc
     */
    public function denormalize(
        FixtureBag $builtFixtures,
        string $className,
        string $fixtureId,
        array $specs,
        FlagBag $flags
    ): FixtureBag {
        return $builtFixtures;
    }

    /**
     * @return string[]
     *
     * @example
     *  'user_{alice, bob}' => [
     *      'user_alice',
     *      'user_bob',
     *  ]
     */
    public function buildIds(string $id): array
    {
        $ids = [];
        $range = $this->buildRange($id);

        $from = $range->getFrom();
        $to = $range->getTo();
        for ($currentIndex = $from; $currentIndex <= $to; $currentIndex++) {
            $ids[
                str_replace(
                    $this->token,
                    $currentIndex,
                    $range->getName()
                )
            ] = $currentIndex;
        }

        return $ids;
    }

    /**
     * @example
     *  'user{1..10}' => new RangeName('user', 1, 10)
     */
    private function buildRange(string $name): RangeName
    {
        $matches = [];
        if (false === $this->canDenormalize($name, $matches)) {
            throw LogicExceptionFactory::createForCannotDenormalizerForChainableFixtureBuilderDenormalizer(__METHOD__);
        }

        $reference = str_replace(
            sprintf('{%s}', $matches['range']),
            $this->token,
            $name
        );

        return new RangeName($reference, (int) $matches['from'], (int) $matches['to']);
    }
}
