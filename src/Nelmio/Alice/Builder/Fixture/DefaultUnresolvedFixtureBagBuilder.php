<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder\Fixture;

use Nelmio\Alice\Builder\UnresolvedFixtureBagBuilderInterface;
use Nelmio\Alice\UnresolvedFixtureBag;

final class DefaultUnresolvedFixtureBagBuilder implements UnresolvedFixtureBagBuilderInterface
{
    /**
     * @var UnresolvedFixtureBuilderInterface
     */
    private $builder;
    
    /**
     * @var FlagParserInterface
     */
    private $flagParser;

    public function __construct(UnresolvedFixtureBuilderInterface $builder, FlagParserInterface $flagParser)
    {
        $this->builder = $builder;
        $this->flagParser = $flagParser;
    }
    
    /**
     * @inheritdoc
     */
    public function build(array $fixtures): UnresolvedFixtureBag
    {
        $bag = new UnresolvedFixtureBag();
        foreach ($fixtures as $className => $fixtureData) {
            foreach ($fixtureData as $reference => $specs) {
                $bag = $this->buildFixture($bag, $className, $reference, $specs);
            }
        }
        
        return $fixtures;
    }
    
    private function buildFixture(
        UnresolvedFixtureBag $fixtures,
        string $className,
        string $reference,
        array $specs
    ): UnresolvedFixtureBag
    {
        $classNameFlags = $this->flagParser->parse($className);
        $referenceFlags = $this->flagParser->parse($reference);
        $flags = $classNameFlags->mergeWith($referenceFlags);
        
        $result = $this->builder->build($fixtures, $classNameFlags->getKey(), $referenceFlags->getKey(), $specs, $flags);
        foreach ($result as $fixture) {
            $fixtures = $fixtures->with($fixture);
        }
        
        return $fixtures;
    }
    
    public function __clone()
    {
        throw new \DomainException();
    }
}
