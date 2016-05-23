<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Fixtures\Definition\Builder;

use Nelmio\Alice\Fixtures\Definition\DefinitionBuilderInterface;

final class BuilderRegistry implements DefinitionBuilderInterface
{
    /**
     * @var ChainableDefinitionBuilderInterface[]
     */
    private $builders;

    /**
     * @param ChainableDefinitionBuilderInterface[] $builders
     */
    public function __construct(array $builders)
    {
        foreach ($builders as $instantiator) {
            if (!($instantiator instanceof ChainableDefinitionBuilderInterface)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expected builders to be "%s" objects. One of the builder was "%s" instead.',
                        ChainableDefinitionBuilderInterface::class,
                        is_object($instantiator) ? get_class($instantiator) : $instantiator
                    )
                );
            }
        }

        $this->builders = $builders;
    }

    /**
     * @inheritdoc
     */
    public function build(string $className, string $name, array $specs): array
    {
        foreach ($this->builders as $builder) {
            if ($builder->canBuild($name)) {
                return $builder->build($className, $name, $specs);
            }
        }
    }
}
