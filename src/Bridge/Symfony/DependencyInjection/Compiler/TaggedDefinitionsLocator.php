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

namespace Nelmio\Alice\Bridge\Symfony\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @private
 */
final class TaggedDefinitionsLocator
{
    /**
     * @return Reference[]
     */
    public function findReferences(ContainerBuilder $container, string $tagName): array
    {
        $taggedServiceIds = $container->findTaggedServiceIds($tagName);

        $taggedReferences = [];
        foreach ($taggedServiceIds as $taggedServiceId => $tags) {
            $taggedReferences[] = new Reference($taggedServiceId);
        }

        return $taggedReferences;
    }
}
