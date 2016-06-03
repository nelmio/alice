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

use Nelmio\Alice\Exception\Builder\NoBuilderFoundException;
use Nelmio\Alice\Fixture\FlagBag;
use Nelmio\Alice\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\UnresolvedFixtureBag;

final class BuilderRegistry implements UnresolvedFixtureBuilderInterface
{
    /**
     * @var ChainableFixtureBuilderInterface[]
     */
    private $builders;

    /**
     * @param ChainableFixtureBuilderInterface[] $builders
     */
    public function __construct(array $builders)
    {
        $this->builders = (
            function (ChainableParameterResolverInterface ...$builders) {
                return $builders;
            }
        )(...$builders);
    }

    /**
     * @inheritdoc
     */
    public function build(string $className, string $reference, array $specs, FlagBag $flags): UnresolvedFixtureBag
    {
        foreach ($this->builders as $builder) {
            if ($builder->canBuild($reference)) {
                return $builder->build($className, $reference, $specs, $flags);
            }
        }
        
        throw new NoBuilderFoundException(
            sprintf(
                'No suitable handler found to handle the fixture with the reference "%s".',
                $reference
            )
        );
    }
}
