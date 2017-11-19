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

namespace Nelmio\Alice\Generator\Resolver\Parameter\Chainable;

use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\IsAServiceTrait;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Throwable\Exception\Generator\Resolver\ResolverNotFoundExceptionFactory;
use Nelmio\Alice\Throwable\Exception\ParameterNotFoundException;
use Nelmio\Alice\Throwable\Exception\ParameterNotFoundExceptionFactory;

final class StringParameterResolver implements ChainableParameterResolverInterface, ParameterResolverAwareInterface
{
    use IsAServiceTrait;

    const PATTERN = '/<{(?<parameter>[^<{]+?)}>/';
    const SINGLE_PARAMETER_PATTERN = '/^<{(?<parameter>(?(?=\{)^[\>]|.)+)}>$/';

    /**
     * @var ParameterResolverInterface|null
     */
    private $resolver;

    public function __construct(ParameterResolverInterface $resolver = null)
    {
        $this->resolver = $resolver;
    }

    /**
     * @inheritdoc
     */
    public function withResolver(ParameterResolverInterface $resolver)
    {
        return new self($resolver);
    }

    /**
     * @inheritdoc
     */
    public function canResolve(Parameter $parameter): bool
    {
        return is_string($parameter->getValue());
    }

    /**
     * {@inheritdoc}
     *
     * @throws ParameterNotFoundException
     */
    public function resolve(
        Parameter $parameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters,
        ResolvingContext $context = null
    ): ParameterBag {
        $context = ResolvingContext::createFrom($context, $parameter->getKey());

        $self = $this;
        $value = preg_replace_callback(
            self::PATTERN,
            function ($match) use ($self, $context, $unresolvedParameters, &$resolvedParameters, $parameter) {
                $key = $match['parameter'];
                $resolvedParameters = $self->resolveStringKey(
                    $self->resolver,
                    $parameter,
                    $key,
                    $unresolvedParameters,
                    $resolvedParameters,
                    $context
                );

                return $resolvedParameters->get($key);
            },
            $parameter->getValue()
        );

        return $resolvedParameters->with($parameter->withValue($value));
    }

    /**
     * @param Parameter                  $parameter Parameter being resolved
     * @param string                     $key       Key of the parameter that need to be resolved to resolve $parameter
     */
    private function resolveStringKey(
        ParameterResolverInterface $resolver = null,
        Parameter $parameter,
        string $key,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters,
        ResolvingContext $context
    ): ParameterBag {
        if ($resolvedParameters->has($key)) {
            return $resolvedParameters;
        }

        if (false === $unresolvedParameters->has($key)) {
            throw ParameterNotFoundExceptionFactory::createForWhenResolvingParameter($key, $parameter);
        }

        $context->checkForCircularReference($key);
        $context->add($key);

        if (null === $resolver) {
            throw ResolverNotFoundExceptionFactory::createUnexpectedCall(__METHOD__);
        }

        return $resolver->resolve(
            new Parameter($key, $unresolvedParameters->get($key)),
            $unresolvedParameters,
            $resolvedParameters,
            $context
        );
    }
}
