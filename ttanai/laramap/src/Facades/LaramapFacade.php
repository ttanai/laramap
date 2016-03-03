<?php namespace Ttanai\Laramap\Facades;

use Illuminate\Support\Facades\Facade;

class LaramapFacade extends Facade {
	protected static function getFacadeAccessor() {
        return 'laramap';
    }
}
