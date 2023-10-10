<?php

namespace Garavel\Routing;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use InvalidArgumentException;
use Garavel\Support\Str;
use Garavel\Support\Facades\Response;
use Garavel\Support\Facades\JsonResponse;
use Garavel\Routing\Exceptions\UnknownController;
use Garavel\Routing\Exceptions\UnknownControllerMethod;
use App\Providers\RouteParamsResolvers;
use Garavel\Http\Response as HttpResponse;

class Route
{
	/**
	 * Route name.
	 */
	public string $name;

	/**
	 * The URI pattern responds to.
	 */
	public string $uri;

	/**
	 * Http methods route supports.
	 */
	public array $methods = [];

	/**
	 * Route expression segment requirements stack.
	 */
	public array $wheres = [];

	/**
	 * Middleware list should be applied to the route.
	 */
	public array $middlewares = [];

	/**
	 * Route controller namespace prefix.
	 */
	public string $namespace = '';

	/**
	 * Router instance.
	 */
	public Router $router;

	/**
	 * Route URI as regular expression.
	 */
	public RouteExpression $expression;

	/**
	 * Route constructor.
	 */
	public function __construct(
		array $methods,
		string $uri,
		public string|array|Closure $handler
	)
	{
		$this->uri = $uri;
		$this->methods = $methods;
		$this->expression = new RouteExpression( $this, $uri );
	}

	/**
	 * Sets router instance.
	 */
	public function setRouter( Router $router ): Route
	{
		$this->router = $router;
		return $this;
	}

	/**
	 * Set route wheres.
	 */
	public function setWheres( array $wheres ): Route
	{
		$this->wheres = $wheres;
		return $this;
	}

	/**
	 * Set route controller namespace.
	 */
	public function setNamespace( string $ns ): Route
	{
		$this->namespace = $ns;
		return $this;
	}

	/**
	 * Set route middlewares.
	 */
	public function setMiddlewares( array $middlewares ): Route
	{
		$this->middlewares = $middlewares;
		return $this;
	}

	/**
	 * Adds a middleware to the middleware stack.
	 */
	public function middleware( string $name ): Route
	{
		$this->middlewares[] = $name;
		return $this;
	}

	/**
	 * Sets route name.
	 */
	public function name( string $name ): Route
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Returns true if the given method name
	 * supported by this route.
	 */
	public function supports( string $methodName ): bool
	{
		return in_array( $methodName, $this->methods );
	}

	/**
	 * Runs the route controller or action.
	 */
	public function run( Matches $matches ): void
	{
		$this->flushActionResult(
			$this->executeAction( $matches )
		);
	}

	/**
	 * Executes the action and returns properly what
	 * returned by the action.
	 */
	private function executeAction( Matches $matches ): mixed
	{
		$handler = $this->handler;

		if( is_string( $handler ))
		{
			if( ! Str::contains( $handler, '@' ))
			{
				$handler .= '@__invoke';
			}

			[ $controllerName, $method ] = Str::split( $handler, '@' );

			return $this->executeController(
				$matches,
				$this->fullyQualifiedControllerNS( $controllerName ),
				$method
			);
		}
		else if( is_array( $handler ))
		{
			return $this->executeController( $matches, ...$handler );
		}
		else if( is_callable( $handler ))
		{
			return $this->runMiddlewares(
				$matches,
				fn() =>
					$handler(
						...$this->resolveArguments(
							$this->getParamReflections( $handler ),
							$matches
						),
						...$matches->values
					)
			);
		}
	}

	/**
	 * It takes a controller name and produce full
	 * namespace path for that controller. 
	 */
	public function fullyQualifiedControllerNS( string $localControllerName ): string
	{
		$ns = 'app\Http\Controllers';
		$ns = $this->namespace
			? "$ns\\$this->namespace\\"
			: "$ns\\";
		
		return Str::startWith( $localControllerName, $ns );
	}

	/**
	 * Executes a controller.
	 * 
	 * @throws UnknownController when the controller class doesn't exists
	 * @throws UnknownControllerMethod when the controller method doesn't exist
	 */
	public function executeController( Matches $matches, string $namespace, string $methodName ): mixed
	{
		if( class_exists( $namespace, autoload: true ) === false )
		{
			throw new UnknownController( $namespace );
		}

		$controller = new $namespace;

		if( method_exists( $controller, $methodName ) === false )
		{
			throw new UnknownControllerMethod( $namespace, $methodName );
		}

		return $this->runMiddlewares( $matches, fn() =>
			$controller->{ $methodName }(
				...$this->resolveArguments(
					$this->getParamReflections( $controller, $methodName ),
					$matches
				),
				...$matches->values
			)
		);
	}

	/**
	 * Runs middlewares and then hits the controller finally.
	 */
	public function runMiddlewares( Matches $matches, Closure $finalAction ): mixed
	{
		if( ! $this->middlewares )
		{
			return $finalAction();
		}

		return $this
			->router
			->resolveMiddlewares( $this->middlewares )[ 0 ]
			->run( $matches, $finalAction );
	}

	/**
	 * Returns given closure or class method's arguments
	 * reflections as array.
	 */
	public function getParamReflections(
		callable|Controller $callableOrController,
		string $methodName = null
	): array
	{
		if( $callableOrController instanceof Controller )
		{
			return ( new ReflectionClass( $callableOrController ))
				->getMethod( $methodName )
				->getParameters();
		}
		else if( is_callable( $callableOrController ))
		{
			return ( new ReflectionFunction( $callableOrController ))
				->getParameters();
		}
	}

	/**
	 * Resolves arguments to their type-hinted classes by using their
	 * resolvers, or combines route matches found in the URL with the
	 * action's arguments that have the same name.
	 */
	public function resolveArguments( array $args, Matches $matches ): array
	{
		$stack = [];
		$resolve = new RouteParamsResolvers;
		$resolve->registerDefaults()->register();

		foreach( $args as $index => $arg )
		{
			$name = $arg->getName();
			$ns = (string) $arg->getType();

			if( RouteParamsResolvers::resolves( $ns ))
			{
				$stack[] = $resolve( $ns, [ $matches->{ $name } ?? null, $matches, $name, $index ]);
			}
			else if( $ns !== '' )
			{
				throw new InvalidArgumentException( "There is no resolver defined for \"$ns\" type." );
			}
			else if( isset( $matches->{ $name }))
			{
				$stack[] = $matches->{ $name };
			}
		}

		return $stack;
	}

	/**
	 * Flushes the action's results properly.
	 */
	private function flushActionResult( mixed $result ): void
	{
		if( $result instanceof HttpResponse )
		{
			$result->flush();
		}
		else if( is_string( $result ) || is_numeric( $result ))
		{
			Response::write( $result )->flush();
		}
		else if( is_bool( $result ) || is_array( $result ) || is_object( $result ))
		{
			JsonResponse::write( $result )->flush();
		}
	}

	/**
	 * Registers a regex modifier for named segments. 
	 */
	public function where( string $name, string $pattern, bool $required = null ): Route
	{
		$this->wheres[ $name ] =
		[
			trim( $pattern, '/~@;%`#' ),
			$required
		];
		
		return $this;
	}

	/**
	 * Initiates a matching process for the represented
	 * route with the given path data.
	 */
	public function match( string $path ): Matches
	{
		return new Matches( $path, $this->expression );
	}
}
