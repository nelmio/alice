<?php

namespace Nelmio\Alice\support\extensions;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Fixtures\Builder\Methods\MethodInterface as BuilderInterface;

class CustomBuilder implements BuilderInterface
{
    public function canBuild($name)
    {
        return $name == 'spec dumped';
    }

    /**
     * this custom builder dumps the given spec
     */
    public function build($class, $name, array $spec)
    {
        return [new Fixture($class, $name, [], null)];
    }
}
