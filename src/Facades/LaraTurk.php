<?php namespace Pauly4it\LaraTurk\Facades;

use \Illuminate\Support\Facades\Facade;

class Laraturk extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laraturk';
    }

}