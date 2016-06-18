<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator\Resolver\Parameter;

use Nelmio\Alice\Exception\Resolver\ParameterNotFoundException;
use Nelmio\Alice\Exception\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Generator\Resolver\ResolvingContext;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Generator\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Generator\Resolver\ParameterResolverInterface;

final class StringParameterResolver implements ChainableParameterResolverInterface, ParameterResolverAwareInterface
{
    const PATTERN = '/<{(?<parameter>[^<{]+?)}>/';
    const SINGLE_PARAMETER_PATTERN = '/^<{(?<parameter>(?(?=\{)^[\>]|.)+)}>$/';

    /**
     * @var ParameterResolverInterface|null
     */
    private $resolver;

    public function withResolver(ParameterResolverInterface $resolver)
    {
        $clone = clone $this;
        $clone->resolver = $resolver;
        
        return $clone;
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
     * @param string $parameter
     * 
     * @throws ParameterNotFoundException
     * @throws ResolverNotFoundException
     */
    public function resolve(
        Parameter $parameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters,
        ResolvingContext $context = null
    ): ParameterBag
    {
        $context = ResolvingContext::createFrom($context, $parameter->getKey());

        $self = $this;
        $value = preg_replace_callback(
            self::PATTERN,
            function ($match) use ($self, $context, $unresolvedParameters, &$resolvedParameters, $parameter) {
                $key = $match['parameter'];
                $resolvedParameters = $self->resolveStringKey($parameter, $key, $unresolvedParameters, $resolvedParameters, $context);

                return $resolvedParameters->get($key);
            },
            $parameter->getValue()
        );

        return $resolvedParameters->with($parameter->withValue($value));
    }

    /**
     * @param Parameter        $parameter Parameter being resolved
     * @param string           $key       Key of the parameter that need to be resolved to resolve $parameter
     * @param ParameterBag     $unresolvedParameters
     * @param ParameterBag     $resolvedParameters
     * @param ResolvingContext $context
     *
     * @throws ParameterNotFoundException
     * @throws ResolverNotFoundException
     *
     * @return ParameterBag
     */
    private function resolveStringKey(
        Parameter $parameter,
        string $key,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters,
        ResolvingContext $context
    ): ParameterBag
    {
        if ($resolvedParameters->has($key)) {
            return $resolvedParameters;
        }

        if (false === $unresolvedParameters->has($key)) {
            throw new ParameterNotFoundException(
                sprintf(
                    'Could not find the parameter "%s" when resolving "%s".',
                    $key,
                    $parameter->getKey()
                )
            );
        }

        $context->checkForCircularReference($key);
        $context = $context->with($key);

        if (null === $this->resolver) {
            throw new ResolverNotFoundException(
                sprintf(
                    'No resolver found to resolve parameter "%s".',
                    $key
                )
            );
        }
        
        return $this->resolver->resolve(
            new Parameter($key, $unresolvedParameters->get($key)),
            $unresolvedParameters,
            $resolvedParameters,
            $context
        );
    }

    public function __clone()
    {
        if (null !== $this->resolver) {
            $this->resolver = clone $this->resolver;
        }
    }
}
