<?php

/*
 * This file is part of the Alice package.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\Generator;

interface ValueResolverAwareInterface
{
    /**
     * @param ValueResolverInterface $resolver
     *
     * @return static
     */
    public function withResolver(ValueResolverInterface $resolver);
}
