<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Fixture;

use Nelmio\Alice\Exception\Builder\RuntimeException;
use Nelmio\Alice\Fixture\UnresolvedFixtureBag;

final class BuilderRegistry implements FixtureBuilderInterface
{
    /**
     * @var ChainableFixtureBuilderInterface[]
     */
    private $builders;

    /**
     * @param ChainableFixtureBuilderInterface[] $builders
     *
     * @throws \InvalidArgumentException When invalid builder is passed.
     */
    public function __construct(array $builders)
    {
        foreach ($builders as $builder) {
            if (false === $builder instanceof ChainableFixtureBuilderInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expected builders to be "%s" objects. Builder "%s" is not.',
                        ChainableFixtureBuilderInterface::class,
                        is_object($builder) ? get_class($builder) : $builder
                    )
                );
            }
        }

        $this->builders = $builders;
    }

    /**
     * Looks for the first suitable builder to builder the given.
     *
     * {@inheritdoc}
     *
     * @throws RuntimeException When no parser is found.
     */
    public function build(string $className, string $name, array $specs): UnresolvedFixtureBag
    {
        foreach ($this->builders as $builder) {
            if ($builder->canBuild($name)) {
                return $builder->build($className, $name, $specs);
            }
        }

        throw new RuntimeException(
            sprintf(
                'No builder supporting the fixture "%s" found',
                $name
            )
        );
    }
}
