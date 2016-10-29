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
final class RegisterTagServicesPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    private $registry;

    /**
     * @var string
     */
    private $tagName;

    /**
     * @var TaggedDefinitionsLocator
     */
    private $taggedDefinitionsLocator;

    public function __construct(string $registry, string $tagName)
    {
        $this->registry = $registry;
        $this->tagName = $tagName;
        $this->taggedDefinitionsLocator = new TaggedDefinitionsLocator();
    }

    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->findDefinition($this->registry);
        $taggedServices = $this->taggedDefinitionsLocator->findReferences($container, $this->tagName);

        $registry->addArgument($taggedServices);
    }
}
