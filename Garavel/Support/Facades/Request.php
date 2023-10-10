<?php

namespace Garavel\Support\Facades;

use Garavel\Http\Request as RealRequest;

class Request extends Facade
{
	/**
	 * Returns request class' namespace.
	 *
	 * @return string
	 */
	public static function getFacadeAccessor(): string
	{
		return RealRequest::class;
	}
}
