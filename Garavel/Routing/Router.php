<?php

namespace Garavel\Routing;

use Closure;
use App\Http\Kernel;
use Garavel\Http\Request;
use Garavel\Routing\Exceptions\MethodNotAllowed;
use Garavel\Routing\Exceptions\NoRouteForRequest;
use Garavel\Support\Facades\JsonResponse;
use Garavel\Support\Facades\Response;

class Router
{
	/**
	 * Group options stack.
	 */
	public RouteGroupCollection $groups;

	/**
	 * Keeps all the routes.
	 */
	public RouteCollection $routes;

	/**
	 * Router constructor.
	 */
	public function __construct()
	{
		$this->routes = new RouteCollection;
		$this->groups = new RouteGroupCollection;
	}

	/**
	 * Adds given route to the stack with GET method.
	 */
	public function get( string $route, string|array|Closure $handler ): Route
	{
		return $this->addRoute([ 'GET', 'HEAD' ], $route, $handler );
	}

	/**
	 * Adds given route to the stack with POST method.
	 */
	public function post( string $route, string|array|Closure $handler ): Route
	{
		return $this->addRoute([ 'POST' ], $route, $handler );
	}

	/**
	 * Adds given route to the stack with PUT method.
	 */
	public function put( string $route, string|array|Closure $handler ): Route
	{
		return $this->addRoute([ 'PUT' ], $route, $handler );
	}

	/**
	 * Adds given route to the stack with PATCH method.
	 */
	public function patch( string $route, string|array|Closure $handler ): Route
	{
		return $this->addRoute([ 'PATCH' ], $route, $handler );
	}

	/**
	 * Adds given route to the stack with DELETE method.
	 */
	public function delete( string $route, string|array|Closure $handler ): Route
	{
		return $this->addRoute([ 'DELETE' ], $route, $handler );
	}

	/**
	 * Adds given route to the stack with OPTIONS method.
	 */
	public function options( string $route, string|array|Closure $handler ): Route
	{
		return $this->addRoute([ 'OPTIONS' ], $route, $handler );
	}

	/**
	 * Adds a route into collection.
	 */
	protected function addRoute( $method, $route, $handler ): Route
	{
		return $this->routes->add(
			$this->createRoute( $method, $route, $handler )
		);
	}

	/**
	 * Creates a route instance.
	 */
	protected function createRoute( $method, $route, $handler ): Route
	{
		$route = $this->groups->prefix( $route );

		return ( new Route( $method, $route, $handler ))
			->setNamespace( $this->groups->namespace())
			->setWheres( $this->groups->wheres())
			->setMiddlewares( $this->groups->middlewares())
			->setRouter( $this );
	}

	/**
	 * Creates a new group.
	 */
	public function group( array $options, callable $callback ): Router
	{
		$this->groups->push( $options );

			$callback();

		$this->groups->pop();

		return $this;
	}

	/**
	 * It tries to find a route that matches the given
	 * request and run it.
	 */
	public function match( Request $request ): void
	{
		try
		{
			$this->routes->match( $request );
		}
		catch( NoRouteForRequest $e )
		{
			$this->responseAsNotFound( $request );
		}
		catch( MethodNotAllowed $e )
		{
			$this->responseAsMethodNotAllowed( $request, $e->getMessage());
		}
	}

	/**
	 * Sends to the client a not found response.
	 */
	public function responseAsNotFound( Request $request ): void
	{
		if( $request->ajax())
		{
			$responser = JsonResponse::fail( 'Unknown resource.', status: 404 );
		}
		else
		{
			$responser = Response::status( 404 )->write( 'Not found.' );
		}

		$responser->flush();
	}

	/**
	 * Sends to the client a method not allowed response.
	 */
	public function responseAsMethodNotAllowed( Request $request, string $msg ): void
	{
		if( $request->ajax())
		{
			$responser = JsonResponse::fail( $msg, status: 405 );
		}
		else
		{
			$responser = Response::status( 405 )->write( $msg );
		}

		$responser->flush();
	}

	/**
	 * It resolves given middleware structure to
	 * natively callable php namespaces.
	 */
	public function resolveMiddlewares( array $middlewares ): array
	{
		$stack = [];

		foreach( $middlewares as $mw )
		{
			// if middleware points to a middleware group
			if( array_key_exists( $mw, Kernel::$groupedMiddlewares ))
			{
				$stack =
				[
					...$stack,
					...$this->resolveMiddlewares( Kernel::$groupedMiddlewares[ $mw ])
				];
			}
			else
			{
				// if middleware points to an alias
				if( array_key_exists( $mw, Kernel::$middlewareAliases ))
				{
					$stack[] = new Middleware( Kernel::$middlewareAliases[ $mw ]);
				}
				// its just fully qualified namespace
				else
				{
					$stack[] = new Middleware( $mw );
				}

				$len = count( $stack );

				if( isset( $stack[ $len - 2 ]))
				{
					$stack[ $len - 2 ]->link( $stack[ $len - 1 ]);
				}
			}
		}

		return $stack;
	}
}
