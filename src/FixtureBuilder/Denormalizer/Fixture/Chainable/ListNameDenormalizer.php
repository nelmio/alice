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

use Nelmio\Alice\Definition\Fixture\SimpleFixtureWithFlags;
use Nelmio\Alice\Definition\Fixture\SimpleFixture;
use Nelmio\Alice\Definition\Fixture\TemplatingFixture;
use Nelmio\Alice\Definition\FlagBag;
use Nelmio\Alice\FixtureBag;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\NotClonableTrait;

final class ListNameDenormalizer extends AbstractChainableDenormalizer
{
    use NotClonableTrait;

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
     * @inheritdoc
     */
    public function denormalize(
        FixtureBag $builtFixtures,
        string $className,
        string $fixtureId,
        array $specs,
        FlagBag $flags
    ): FixtureBag
    {
        $this->checkFlagParser(__METHOD__);
        $flags = $this->parser->parse($fixtureId)->mergeWith($flags, false);
        $fixtureId = $flags->getKey();

        /**
         * @var FixtureInterface $tempFixture
         * @var FixtureBag       $builtFixtures
         */
        list($tempFixture, $builtFixtures) = $this->denormalizeTemporaryFixture(
            $builtFixtures,
            $className,
            $specs,
            $flags->withKey($fixtureId)
        );
        $fixtureIds = $this->buildIds($fixtureId);

        foreach ($fixtureIds as $fixtureId) {
            $builtFixtures = $builtFixtures->with(
                new TemplatingFixture(
                    new SimpleFixtureWithFlags(
                        new SimpleFixture(
                            $fixtureId,
                            $tempFixture->getClassName(),
                            $tempFixture->getSpecs()
                        ),
                        $flags->withKey($fixtureId)
                    )
                )
            );
        }

        return $builtFixtures;
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
    private function buildIds(string $id): array
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
            $ids[] = str_replace(
                sprintf('{%s}', $matches['list']),
                $element,
                $id
            );
        }

        return $ids;
    }
}
