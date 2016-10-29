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

class DummyAbstractChainableDenormalizer extends AbstractChainableDenormalizer
{
    /**
     * @inheritdoc
     */
    public function canDenormalize(string $reference): bool
    {
        return 'user' === $reference;
    }

    /**
     * @param string $id
     *
     * @return string[]
     *
     * @example
     *  'user_{alice, bob}' will result in:
     *  [
     *      'user_alice' => 'alice',
     *      'user_bob' => 'bob',
     *  ]
     */
    public function buildIds(string $id): array
    {
        return ['user' => 'resu'];
    }
}
