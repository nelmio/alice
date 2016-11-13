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

namespace Nelmio\Alice\Bridge\Symfony\DependencyInjection;

use Nelmio\Alice\Exception\InvalidArgumentExceptionFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @private
 */
final class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('nelmio_alice');
        $rootNode
            ->children()
                ->scalarNode('locale')
                    ->defaultValue('en_US')
                    ->info('Default locale for the Faker Generator')
                ->end()
                ->scalarNode('seed')
                    ->defaultValue(1)
                    ->info('Value used make sure Faker generates data consistently across runs, set to null to disable.')
                    ->validate()
                        ->always(
                            function ($seed) {
                                if (null === $seed || (is_int($seed) && $seed > 0)) {
                                    return $seed;
                                }

                                throw InvalidArgumentExceptionFactory::createForInvalidSeedConfigurationValue($seed);
                            }
                        )
                    ->end()
                ->end()
            ->scalarNode('loading_limit')
                ->defaultValue(5)
                ->info('Alice may do some recursion to resolve certain values. This parameter defines a limit which '
                    .'will stop the resolution once reached.')
            ->end()
            ->scalarNode('max_unique_values_retries')
                ->defaultValue(150)
                ->info('Maximum number of time Alice can try to generate a unique value before stopping and failing.')
            ->end()
        ;
        return $treeBuilder;
    }
}
