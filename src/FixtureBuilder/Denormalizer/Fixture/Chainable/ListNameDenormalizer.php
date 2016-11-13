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

use Nelmio\Alice\Throwable\Exception\LogicExceptionFactory;

final class ListNameDenormalizer extends AbstractChainableDenormalizer
{
    /** @private */
    const REGEX = '/\{(?<list>[^,\s]+(?:,\s[^,\s]+)+)\}/';

    /**
     * @inheritdoc
     */
    public function canDenormalize(string $reference, array &$matches = []): bool
    {
        return 1 === preg_match(self::REGEX, $reference, $matches);
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
