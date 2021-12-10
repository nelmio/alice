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

use Closure;
use Nelmio\Alice\Throwable\Exception\InvalidArgumentExceptionFactory;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @private
 */
final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('nelmio_alice');

        $treeBuilder
            ->getRootNode()
            ->children()
                ->scalarNode('locale')
                    ->defaultValue('en_US')
                    ->info('Default locale for the Faker Generator')
                    ->validate()
                        ->always($this->createStringValidatorClosure())
                    ->end()
                ->end()
                ->scalarNode('seed')
                    ->defaultValue(1)
                    ->info('Value used make sure Faker generates data consistently across runs, set to null to disable.')
                    ->validate()
                        ->always(
                            static function ($seed) {
                                if (null === $seed || (is_int($seed) && $seed > 0)) {
                                    return $seed;
                                }

                                throw InvalidArgumentExceptionFactory::createForInvalidSeedConfigurationValue($seed);
                            }
                        )
                    ->end()
                ->end()
                ->arrayNode('functions_blacklist')
                    ->scalarPrototype()->end()
                    ->defaultValue(['current'])
                    ->info(
                        'Some PHP native functions may conflict with Faker formatters. By default, PHP native '
                        .'functions are used over Faker formatters. If you want to change that, simply blacklist the '
                        .'PHP function.'
                    )
                    ->validate()
                        ->always(
                            static function (array $value) {
                                foreach ($value as $item) {
                                    if (false === is_string($item)) {
                                        throw InvalidArgumentExceptionFactory::createForExpectedConfigurationArrayOfStringValue($item);
                                    }
                                }

                                return $value;
                            }
                        )
                    ->end()
                ->end()
                ->integerNode('loading_limit')
                    ->defaultValue(5)
                    ->info(
                        'Alice may do some recursion to resolve certain values. This parameter defines a limit which '
                        .'will stop the resolution once reached.'
                    )
                    ->validate()
                        ->always($this->createPositiveIntegerValidatorClosure())
                    ->end()
                ->end()
                ->integerNode('max_unique_values_retry')
                    ->defaultValue(150)
                    ->info('Maximum number of time Alice can try to generate a unique value before stopping and failing.')
                    ->validate()
                        ->always($this->createPositiveIntegerValidatorClosure())
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    private function createStringValidatorClosure(): Closure
    {
        return static function ($value) {
            if (is_string($value)) {
                return $value;
            }

            throw InvalidArgumentExceptionFactory::createForExpectedConfigurationStringValue($value);
        };
    }

    private function createPositiveIntegerValidatorClosure(): Closure
    {
        return static function ($value) {
            if (is_int($value) && 0 < $value) {
                return $value;
            }

            throw InvalidArgumentExceptionFactory::createForExpectedConfigurationPositiveIntegerValue($value);
        };
    }
}
