<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\FixtureBuilder;

use Nelmio\Alice\FixtureBuilderInterface;
use Nelmio\Alice\FixtureSet;
use Nelmio\Alice\NotClonableTrait;
use Nelmio\Alice\ObjectBag;
use Nelmio\Alice\ParameterBag;

final class SimpleBuilder implements FixtureBuilderInterface
{
    use NotClonableTrait;
    
    /**
     * @var ParserInterface
     */
    private $parser;
    
    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    public function __construct(ParserInterface $parser, DenormalizerInterface $denormalizer)
    {
        $this->parser = $parser;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @inheritdoc
     */
    public function build(string $file, array $parameters = [], array $objects = []): FixtureSet
    {
        $data = $this->parser->parse($file);
        $bareFixtureSet = $this->denormalizer->denormalize($data);
        
        return new FixtureSet(
            $bareFixtureSet->getParameters(),
            new ParameterBag($parameters),
            $bareFixtureSet->getFixtures(),
            new ObjectBag($objects)
        );
    }
}
