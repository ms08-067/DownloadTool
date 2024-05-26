<?php

namespace App\Loggy\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Loggy
 * @package App\Loggy\Facades
 */
class Loggy extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'loggy';
    }
}
