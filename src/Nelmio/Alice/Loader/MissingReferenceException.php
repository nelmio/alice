<?php

namespace Nelmio\Alice\Loader;

class MissingReferenceException extends \UnexpectedValueException {
    const FORWARD = 0;
    const DEFERRED = 1;
}
