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

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @private
 */
final class RegisterFakerProvidersPass implements CompilerPassInterface
{
    /**
     * @var TaggedDefinitionsLocator
     */
    private $taggedDefinitionsLocator;

    public function __construct()
    {
        $this->taggedDefinitionsLocator = new TaggedDefinitionsLocator();
    }
    
    public function process(ContainerBuilder $container): void
    {
        $fakerGenerator = $container->findDefinition('nelmio_alice.faker.generator');
        $providers = $this->taggedDefinitionsLocator->findReferences($container, 'nelmio_alice.faker.provider');

        foreach ($providers as $provider) {
            $fakerGenerator->addMethodCall('addProvider', [$provider]);
        }
    }
}
