<?php namespace Xeor\OctoCart\Facades;

use October\Rain\Support\Facade;

class Cart extends Facade
{
    /**
     * Get the registered name of the component.
     * @return string
     */
    protected static function getFacadeAccessor() { return 'cart'; }
}
