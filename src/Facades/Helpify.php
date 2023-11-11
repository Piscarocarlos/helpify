<?php

namespace Piscarocarlos\Helpify\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Piscarocarlos\Helpify\Helpify
 */
class Helpify extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Piscarocarlos\Helpify\Helpify::class;
    }
}
