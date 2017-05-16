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

namespace Nelmio\Alice\Generator\Caller;

use Nelmio\Alice\Definition\MethodCall\SimpleMethodCall;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Definition\ValueInterface;
use Nelmio\Alice\FixtureInterface;
use Nelmio\Alice\Generator\Caller\ChainableCallProcessorInterface;
use Nelmio\Alice\Generator\CallerInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\Generator\ValueResolverAwareInterface;
use Nelmio\Alice\Generator\ValueResolverInterface;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectInterface;
use Nelmio\Alice\Throwable\Exception\Generator\Caller\CallProcessorExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\UnresolvableValueDuringGenerationExceptionFactory;
use Nelmio\Alice\Throwable\InstantiationThrowable;
use Nelmio\Alice\Throwable\ResolutionThrowable;

final class CallProcessorRegistry implements CallProcessorInterface
{
    use IsAServiceTrait;

    /**
     * @var ChainableCallProcessorInterface[]
     */
    private $processors = [];

    public function __construct(array $processors)
    {
        $processors = (function (ChainableCallProcessorInterface ...$processors) { return $processors; })(...$processors);

        foreach ($processors as $processor) {
            $this->processors[] = $processor instanceof CallProcessorAwareInterface
                ? $processor->withProcessor($this)
                : $processor
            ;
        }
    }

    public function process(
        ObjectInterface $object,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context,
        MethodCallInterface $methodCall
    ): ResolvedFixtureSet
    {
        foreach ($this->processors as $processor) {
            if ($processor->canProcess($methodCall)) {
                return $processor->process($object, $fixtureSet, $context, $methodCall);
            }
        }

        throw CallProcessorExceptionFactory::createForNoParserFoundForElement($methodCall);
    }
}
