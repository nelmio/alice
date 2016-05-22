<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Resolver\Parameter;

use Nelmio\Alice\Exception\Resolver\ParameterNotFoundException;
use Nelmio\Alice\Exception\Resolver\ResolverNotFoundException;
use Nelmio\Alice\Parameter;
use Nelmio\Alice\ParameterBag;
use Nelmio\Alice\Resolver\ChainableParameterResolverInterface;
use Nelmio\Alice\Resolver\ParameterResolverAwareInterface;
use Nelmio\Alice\Resolver\ParameterResolverInterface;

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
     * @param bool|int|float $parameter
     */
    public function resolve(
        Parameter $parameter,
        ParameterBag $unresolvedParameters,
        ParameterBag $resolvedParameters,
        ResolvingContext $context = null
    ): ParameterBag
    {
        $context = $this->getContext($parameter, $context);

        $self = $this;
        $result = new ParameterBag();
        $value = preg_replace_callback(
            self::PATTERN,
            function ($match) use ($self, $context, $unresolvedParameters, $resolvedParameters, $parameter, &$result) {
                $key = $match['parameter'];

                $resolvedBag = $self->resolveStringKey($parameter, $key, $unresolvedParameters, $resolvedParameters, $context);

                $resolvedKey = $resolvedBag->get($key);
                foreach ($resolvedBag as $paramKey => $paramValue) {
                    $result = $result->with(new Parameter($paramKey, $paramValue));
                }

                return $resolvedKey;
            },
            $parameter->getValue()
        );

        return $result->with($parameter->withValue($value));
    }

    private function getContext(Parameter $parameter, ResolvingContext $context = null): ResolvingContext
    {
        $key = $parameter->getKey();

        if (null === $context) {
            $context = new ResolvingContext($key);
        }

        if (false === $context->has($key)) {
            $context = $context->with($key);
        }

        return $context;
    }

    /**
     * @param Parameter        $parameter Parameter being resolved
     * @param string           $key       Key of the parameter that need to be resolved to resolve $parameter
     * @param ParameterBag     $unresolvedParameters
     * @param ParameterBag     $resolvedParameters
     * @param ResolvingContext $context
     *
     * @return array|mixed|ParameterBag
     * @throws ParameterNotFoundException
     * @throws \Nelmio\Alice\Exception\Resolver\CircularReferenceException
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
            return new ParameterBag([$key => $resolvedParameters->get($key)]);
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
}
