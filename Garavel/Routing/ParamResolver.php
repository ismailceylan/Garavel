<?php

namespace Garavel\Routing;

use Closure;
use Garavel\Support\Arr;
use RuntimeException;
use Garavel\Support\Str;

class ParamResolver
{
	/**
	 * Keeps resolver callables for classes.
	 */
	public static array $resolvers = [];

	/**
	 * Runs a resolver.
	 *
	 * @throws RuntimeException when there is not a resolver for the given class
	 */
	public function __invoke( string $class, array $args ): mixed
	{
		if( ! self::resolves( $class ))
		{
			throw new RuntimeException(
				"There is no resolver defined for $class class."
			);
		}

		$resolver = self::$resolvers[ $class ];

		return is_callable( $resolver )
			? $resolver( ...$args )
			: $resolver;
	}

	/**
	 * Returns whether given class has a resolver method or not.
	 */
	public static function resolves( string $class ): bool
	{
		return array_key_exists( $class, self::$resolvers );
	}

	/**
	 * Adds new resolver.
	 */
	public static function resolve( string $class, mixed $resolver ): void
	{
		self::$resolvers[ $class ] = $resolver;
	}

	/**
	 * Register resolvers.
	 */
	public function register()
	{
		// 
	}

	/**
	 * Registers default resolvers.
	 */
	public function registerDefaults(): ParamResolver
	{
		self::resolve( \Garavel\Http\Request::class, request());
		self::resolve( \Garavel\Http\Response::class, response());
		self::resolve( \Garavel\Http\JsonResponse::class, jsonResponse());

		self::resolve( 'string', fn( string $value ) => $value );
		self::resolve( 'int', fn( string $value ) => (int) $value );
		self::resolve( 'bool', fn( string $value ) => Str::parseBool( $value ));
		self::resolve( 'array', fn( string $value ) =>
			Str::isArrayable( $value )
				? Arr::normalize( Str::split( $value, Str::splitter( $value )))
				: Arr::normalize([ $value ])
		);

		return $this;
	}
}
