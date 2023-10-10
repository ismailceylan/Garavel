<?php

namespace Garavel\Routing;

use Garavel\Support\Arr;
use Garavel\Http\Request;
use Garavel\Routing\Exceptions\MethodNotAllowed;
use Garavel\Support\Facades\Response;
use Garavel\Support\Facades\JsonResponse;
use Garavel\Routing\Exceptions\NoRouteForRequest;

class RouteCollection
{
	/**
	 * Route list.
	 *
	 * @var array
	 */
	public array $routes = [];

	/**
	 * Adds a new route in the collection.
	 *
	 * @param Route
	 * @return Route
	 */
	public function add( Route $route ): Route
	{
		return $this->routes[] = $route;
	}

	/**
	 * It finds a route that matches the requested path and
	 * http method and triggers the route action.
	 *
	 * @param Request $request
	 * @throws NoRouteForRequest when not found a route matches with request
	 * @throws MethodNotAllowed when found a route but the request method is not supported by them
	 * @return void
	 */
	public function match( Request $request ): void
	{
		$matches = [];
		$requestMethod = $request->method();

		foreach( $this->routes as $route )
		{
			$match = $route->match(
				$request->path() ?: '/'
			);

			// if route matches with requested path
			if( $match->hasMatched )
			{
				// if route supports the requested method
				if( $route->supports( $requestMethod ))
				{
					$route->run( $match );
					return;
				}

				$matches[] = $route;
			}
		}

		// If the $matches array is not empty, this means that there are route(s)
		// that match the requested path, but none of them support the request method
		if( ! empty( $matches ))
		{
			// if the requested method OPTIONS
			if( $requestMethod === 'OPTIONS' )
			{
				$this->responseToOptionsRequest( $matches, $request );
				return;
			}

			throw new MethodNotAllowed(
				"$requestMethod method not allowed. You can only use: " .
				Arr::join( Arr::flat( Arr::pluck( $matches, 'methods' )))
			);
		}

		throw new NoRouteForRequest;
	}

	/**
	 * Responses to Http OPTIONS requests.
	 */
	public function responseToOptionsRequest( array $matches, Request $request ): void
	{
		$methods = Arr::flat( Arr::pluck( $matches, 'methods' ));
		$strMethods = Arr::join( $methods );

		if( $request->ajax())
		{
			$responser = JsonResponse::write( $methods );
		}
		else
		{
			$responser = Response::write( $strMethods );
		}

		$responser
			->status( 200 )
			->header( 'Allow', $strMethods )
			->header( 'Access-Control-Allow-Methods', $strMethods )
			->flush();
	}
}
