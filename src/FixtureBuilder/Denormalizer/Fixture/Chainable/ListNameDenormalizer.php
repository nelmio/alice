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

final class ListNameDenormalizer extends AbstractChainableDenormalizer
{
    /** @internal */
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
            throw new \LogicException(
                sprintf(
                    'As a chainable denormalizer, "%s" should be called only if "::canDenormalize() returns true. Got '
                    .'false instead.',
                    __METHOD__
                )
            );
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
