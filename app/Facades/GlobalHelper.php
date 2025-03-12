<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class GlobalHelper extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'globalhelper';
    }
}
