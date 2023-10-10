<?php

namespace Garavel\Support\Facades;

use Garavel\Routing\Router;

class Route extends Facade
{
	/**
	 * Returns route class' namespace.
	 *
	 * @return string
	 */
	public static function getFacadeAccessor(): string
	{
		return Router::class;
	}
}
