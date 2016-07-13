<?php

/*
 * This file is part of the Alice package.
 *  
 * (c) Nelmio <hello@nelm.io>
 *  
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\Alice\support\models\typehint;

class DummyWithInterface
{
    public $data;
    
    public function setRelatedDummy(RelatedDummyInterface $relatedDummy)
    {
        $this->data = $relatedDummy;
    }
}
