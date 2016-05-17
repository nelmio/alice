<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Builder;

use Nelmio\Alice\BuilderInterface;
use Nelmio\Alice\Exception\Builder\RuntimeException;
use Nelmio\Alice\Fixture\FixtureSet;

final class DefaultBuilder implements BuilderInterface
{
    /**
     * @var IncludeProcessor
     */
    private $includeProcessor;

    /**
     * @var ResolvedFixtureBuilder
     */
    private $fixtureBuilder;

    /**
     * @var ParameterBuilder
     */
    private $parameterBuilder;

    public function __construct(
        IncludeProcessor $includeProcessor,
        ParameterBuilder $parameterBuilder,
        ResolvedFixtureBuilder $fixtureBuilder
    ) {
        $this->includeProcessor = $includeProcessor;
        $this->parameterBuilder = $parameterBuilder;
        $this->fixtureBuilder = $fixtureBuilder;
    }

    /**
     * Looks for the first suitable builder to builder the given.
     *
     * {@inheritdoc}
     *
     * @throws RuntimeException When no parser is found.
     */
    public function build(array $data): FixtureSet
    {
        $processedData = $this->includeProcessor->process($data);

        $parameters = $this->parameterBuilder->build($processedData);
        $unresolvedFixtures = $this->fixtureBuilder->build($processedData);

        return new FixtureSet($parameters, $unresolvedFixtures);
    }
}
