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

use Nelmio\Alice\Definition\RangeName;
use Nelmio\Alice\FixtureBuilder\Denormalizer\Fixture\FixtureDenormalizerInterface;
use Nelmio\Alice\FixtureBuilder\Denormalizer\FlagParserInterface;

final class RangeNameDenormalizer extends AbstractChainableDenormalizer
{
    /** @private */
    const REGEX = '/.+\{(?<range>(?<from>[0-9]+)(?:\.{2})(?<to>[0-9]+))\}/';

    /**
     * @var string Unique token
     */
    private $token;

    public function __construct(FixtureDenormalizerInterface $denormalizer = null, FlagParserInterface $parser = null)
    {
        parent::__construct($denormalizer, $parser);

        $this->token = uniqid(__CLASS__);
    }

    /**
     * @inheritdoc
     */
    public function canDenormalize(string $name, array &$matches = []): bool
    {
        return 1 === preg_match(self::REGEX, $name, $matches);
    }

    /**
     * @param string $id
     *
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
        $reference = str_replace(
            sprintf('{%s}', $matches['range']),
            $this->token,
            $name
        );

        return new RangeName($reference, (int) $matches['from'], (int) $matches['to']);
    }
}
