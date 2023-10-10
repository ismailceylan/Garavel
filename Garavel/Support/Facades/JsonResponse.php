<?php

namespace Garavel\Support\Facades;

use Garavel\Http\JsonResponse as RealResponse;

class JsonResponse extends Facade
{
	/**
	 * Returns json response class' namespace.
	 *
	 * @return string
	 */
	public static function getFacadeAccessor(): string
	{
		return RealResponse::class;
	}
}
