<?php

namespace Nelmio\Alice\support\extensions;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Instantiator\Methods\MethodInterface as InstantiatorInterface;

class CustomInstantiator implements InstantiatorInterface
{
    public function canInstantiate(Fixture $fixture)
    {
        return preg_match('/User/', $fixture->getClass());
    }

    /**
     * this custom instantiator dumps the given spec
     */
    public function instantiate(Fixture $fixture)
    {
        $class = $fixture->getClass();
        $newObj = new $class();
        $newObj->uuid = uniqid();

        return $newObj;
    }
}
