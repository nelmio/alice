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
    public const REGEX = '/.+\{(?<range>(?<from>\d+)(?:\.{2})(?<to>\d+)((,\s?(?<step>\d+))?))\}/';

    /**
     * @var string Unique token
     */
    private $token;

    public function __construct()
    {
        $this->token = uniqid(__CLASS__, true);
    }

    public function canDenormalize(string $name, array &$matches = []): bool
    {
        return 1 === preg_match(self::REGEX, $name, $matches);
    }

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
     * @return array<string, int>
     *
     * @example
     *  'user_{1..2}' => [
     *      'user_1',
     *      'user_2',
     *  ]
     * @example
     *  'user_{1..3, 2}' => [
     *      'user_1',
     *      'user_3',
     *  ]
     */
    public function buildIds(string $id): array
    {
        $ids = [];
        $range = $this->buildRange($id);

        $from = $range->getFrom();
        $to = $range->getTo();
        $step = $range->getStep();
        for ($currentIndex = $from; $currentIndex <= $to; $currentIndex += $step) {
            $ids[
                str_replace(
                    $this->token,
                    (string) $currentIndex,
                    $range->getName()
                )
            ] = $currentIndex;
        }

        return $ids;
    }

    /**
     * @example
     *  'user{1..10}' => new RangeName('user', 1, 10)
     *  'user{1..10, 2}' => new RangeName('user', 1, 10, 2)
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

        $step = 1;

        if (isset($matches['step'])) {
            $step = ((int) $matches['step']) ?: 1;
        }

        return new RangeName($reference, (int) $matches['from'], (int) $matches['to'], $step);
    }
}
