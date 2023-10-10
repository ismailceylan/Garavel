<?php

namespace Garavel\Routing;

use Closure;

class Middleware
{
	/**
	 * Next middleware.
	 */
	public Middleware $next;

	/**
	 * Fully qualified namespace of middleware class.
	 */
	public string $middleware;

	/**
	 * Middleware.
	 */
	public function __construct( string $middleware )
	{
		$this->middleware = $middleware;
	}

	/**
	 * Links this and the given middleware to each other. 
	 */
	public function link( Middleware $next )
	{
		$this->next = $next;
	}

	/**
	 * Executes middleware.
	 */
	public function run( Matches $matches, Closure $final ): mixed
	{
		// boot methods can decide whether to move the request forward or to fail
		// so we will pack it how the process of moving forward is performed
		$next = fn() => isset( $this->next )
			// boot method called this closure and
			// there is another middleware after this
			// we are gonna call the next middleware
			? $this->next->run( $matches, $final )
			// we consumed all the middlewares and still we
			// are here so we should call the controller action
			: $final();
		
		return ( new $this->middleware )->boot( $next, $matches );
	}
}
