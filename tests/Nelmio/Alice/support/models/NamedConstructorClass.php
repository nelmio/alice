<?php

namespace Nelmio\Alice\support\models;

class NamedConstructorClass
{
   public $lambda;

   private function __construct($lambda)
   {
       $this->lambda = $lambda;
   }

   public static function withLambda($lambda)
   {
       return new self($lambda);
   }
}
