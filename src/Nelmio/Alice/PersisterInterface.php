<?php

namespace Nelmio\Alice;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
interface PersisterInterface
{
    public function persist($object);
}
