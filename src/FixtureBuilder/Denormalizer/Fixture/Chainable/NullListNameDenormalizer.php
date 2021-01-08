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
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Throwable\Exception\LogicExceptionFactory;

final class NullListNameDenormalizer implements CollectionDenormalizer
{
    use IsAServiceTrait;

    /** @private */
    const REGEX = '/\{(?<list>[\p{L}\d\._\/]+(?:,\s[^,\s]+)*)\}/u';
    
    public function canDenormalize(string $reference, array &$matches = []): bool
    {
        if (1 === preg_match(self::REGEX, $reference, $matches)) {
            return false === strpos($matches['list'], '..');
        }

        return false;
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
        $matches = [];
        if (false === $this->canDenormalize($id, $matches)) {
            throw LogicExceptionFactory::createForCannotDenormalizerForChainableFixtureBuilderDenormalizer(__METHOD__);
        }

        $listElements = preg_split('/\s*,\s*/', $matches['list']);

        $ids = [];
        foreach ($listElements as $element) {
            $ids[
                str_replace(
                    sprintf('{%s}', $matches['list']),
                    $element,
                    $id
                )
            ] = $element;
        }

        return $ids;
    }
}
