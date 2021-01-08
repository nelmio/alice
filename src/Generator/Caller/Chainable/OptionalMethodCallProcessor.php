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

namespace Nelmio\Alice\Generator\Caller\Chainable;

use LogicException;
use Nelmio\Alice\Definition\MethodCall\OptionalMethodCall;
use Nelmio\Alice\Definition\MethodCallInterface;
use Nelmio\Alice\Generator\Caller\CallProcessorAwareInterface;
use Nelmio\Alice\Generator\Caller\CallProcessorInterface;
use Nelmio\Alice\Generator\Caller\ChainableCallProcessorInterface;
use Nelmio\Alice\Generator\GenerationContext;
use Nelmio\Alice\Generator\ResolvedFixtureSet;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\ObjectInterface;

final class OptionalMethodCallProcessor implements ChainableCallProcessorInterface, CallProcessorAwareInterface
{
    use IsAServiceTrait;

    /**
     * @var CallProcessorInterface
     */
    private $processor;

    public function __construct(CallProcessorInterface $processor = null)
    {
        $this->processor = $processor;
    }
    
    public function withProcessor(CallProcessorInterface $processor): self
    {
        return new self($processor);
    }
    
    public function canProcess(MethodCallInterface $methodCall): bool
    {
        return $methodCall instanceof OptionalMethodCall;
    }

    /**
     * @param OptionalMethodCall $methodCall
     */
    public function process(
        ObjectInterface $object,
        ResolvedFixtureSet $fixtureSet,
        GenerationContext $context,
        MethodCallInterface $methodCall
    ): ResolvedFixtureSet {
        if (false === ($methodCall instanceof OptionalMethodCall)) {
            throw new LogicException('TODO');
        }

        if (null === $this->processor) {
            throw new LogicException('TODO');
        }

        if (mt_rand(0, 99) >= $methodCall->getPercentage()) {
            return $fixtureSet;
        }

        return $this->processor->process(
            $object,
            $fixtureSet,
            $context,
            $methodCall->getOriginalMethodCall()
        );
    }
}
