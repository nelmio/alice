<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Instances\Instantiator;

use Nelmio\Alice\Exception\Instantiator\RuntimeException;
use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\InstantiatorInterface;

/**
 * @author Théo FIDRY <theo.fidry@gmail.com>
 */
final class InstantiatorRegistry implements InstantiatorInterface
{
    /**
     * @var ChainableInstantiatorInterface[]
     */
    private $instantiators;
    
    /**
     * @param ChainableInstantiatorInterface[] $instantiators
     * 
     * @throws \InvalidArgumentException When invalid instantiator is passed.
     */
    public function __construct(array $instantiators)
    {
        foreach ($instantiators as $instantiator) {
            if (!($instantiator instanceof ChainableInstantiatorInterface)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Expected instantiators to be "%s" objects. One of the instantiator was "%s" instead.',
                        ChainableInstantiatorInterface::class,
                        is_object($instantiator) ? get_class($instantiator) : $instantiator
                    )
                );
            }
        }

        $this->instantiators = $instantiators;
    }
    
    /**
     * @inheritdoc
     */
    public function instantiate(Fixture $fixture)
    {
        foreach ($this->instantiators as $instantiator) {
            if ($instantiator->canInstantiate($fixture)) {
                return $instantiator->instantiate($fixture);
            }
        }
        
        throw new RuntimeException(
            sprintf(
                'No instantiator supporting Fixture for "%s" found',
                $fixture->getClass()
            )
        );
    }
}
