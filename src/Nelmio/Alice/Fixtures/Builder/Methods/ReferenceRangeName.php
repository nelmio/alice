<?php

namespace Nelmio\Alice\Fixtures\Builder\Methods;

use Nelmio\Alice\Fixtures\Fixture;
use Nelmio\Alice\Instances\Collection;

class ReferenceRangeName implements MethodInterface
{
    private $matches = [];

    /**
     * @var \Nelmio\Alice\Instances\Collection
     */
    private $objects;

    /**
     * ReferenceRangeName constructor.
     * @param \Nelmio\Alice\Instances\Collection $objects
     */
    public function __construct(Collection $objects)
    {
        $this->objects = $objects;
    }

    /**
     * {@inheritDoc}
     */
    public function canBuild($name)
    {
        return 1 === preg_match('#\{@([0-9a-zA-Z\._\-]+)(\*?+)\}#i', $name, $this->matches);
    }

    /**
     * {@inheritDoc}
     */
    public function build($class, $name, array $spec)
    {
        $fixtures = [];

        $referenceName = $this->matches[1];
        $referenceAll = $this->matches[2] == '*';

        if($referenceAll) {
            $keys = $this->objects->getKeysByMask($referenceName.".+");
            foreach($keys as $currentIndex => $key) {
                $instance = $this->objects->find($key);
                $currentName = str_replace($this->matches[0], $key, $name);
                $fixture = new Fixture($class, $currentName, $spec, $instance);
                $fixtures[] = $fixture;
            }
        }
        else {
            $currentValue = $this->objects->get($this->matches[1]);
            if(is_null($currentValue)) {
                throw new \UnexpectedValueException(
                    sprintf('Instance %s is not defined!', $this->matches[1])
                );
            }
            $currentName = str_replace($this->matches[0], $referenceName, $name);
            $fixture = new Fixture($class, $currentName, $spec, $currentValue);
            $fixtures[] = $fixture;
        }

        return $fixtures;
    }
}