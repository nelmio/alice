<?php

/*
 * This file is part of the Alice package.
 *
 *  (c) Nelmio <hello@nelm.io>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Fixture\Chainable;

use Nelmio\Alice\Builder\Fixture\ChainableFixtureBuilderInterface;
use Nelmio\Alice\Builder\Fixture\RawFlagParser;
use Nelmio\Alice\Fixture\FlagBag;
use Nelmio\Alice\Fixture\MethodCallDefinition;
use Nelmio\Alice\Fixture\PropertyDefinition;
use Nelmio\Alice\Fixture\PropertyDefinitionBag;
use Nelmio\Alice\Fixture\SpecificationBag;
use Nelmio\Alice\UnresolvedFixture;
use Nelmio\Alice\UnresolvedFixtureBag;

final class SimpleNameBuilder implements ChainableFixtureBuilderInterface
{
    /**
     * @var RawFlagParser
     */
    private $flagParser;

    public function __construct(RawFlagParser $flagParser)
    {
        $this->flagParser = $flagParser;
    }

    /**
     * @inheritdoc
     */
    public function canBuild(string $reference): bool
    {
        return false === strpos($reference, '{');
    }

    /**
     * @inheritdoc
     */
    public function build(UnresolvedFixtureBag $builtFixtures, string $className, string $reference, array $specs, FlagBag $flags): UnresolvedFixtureBag
    {
        $fixture = new UnresolvedFixture(
            $reference,
            $className,
            $this->createSpecs($specs),
            $flags
        );

        return $builtFixtures->with($fixture);
    }

    /**
     * @param array $specs
     *
     * @throws \InvalidArgumentException
     * 
     * @return SpecificationBag
     */
    private function createSpecs(array $specs): SpecificationBag
    {
        $constructor = null;
        $properties = new PropertyDefinitionBag();
        $calls = [];

        foreach ($specs as $propertyWithFlags => $value) {
            $flags = $this->flagParser->parse($propertyWithFlags);
            $property = $flags[0];
            $requiresUnique = in_array('unique', $flags[1]);
            
            if ('__construct' === $property) {
                $constructor = new MethodCallDefinition($property, $value, $requiresUnique);
                
                continue;
            }

            if ('__calls' === $property) {
                $calls = $this->createMethodCalls($value);

                continue;
            }
            
            $properties = $properties->with(new PropertyDefinition($property, $value, $requiresUnique));
        }
        
        return new SpecificationBag($constructor, $properties, $calls);
    }

    /**
     * @param array<string, mixed> $calls
     *
     * @throws \InvalidArgumentException
     *             
     * @return MethodCallDefinition[]
     */
    private function createMethodCalls(array $calls): array
    {
        $methodCalls = [];
        foreach ($calls as $methodWithFlags => $arguments) {
            $flags = $this->flagParser->parse($methodWithFlags);
            $method = $flags[0];
            $requiresUnique = in_array('unique', $flags[1]);

            $methodCalls[] = new MethodCallDefinition($method, $arguments, $requiresUnique);
        }
        
        return $methodCalls;
    }
    
    public function __clone()
    {
        throw new \DomainException();
    }
}
