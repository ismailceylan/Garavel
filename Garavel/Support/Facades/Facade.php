<?php

namespace Garavel\Support\Facades;

use RuntimeException;

class Facade
{
	/**
	 * Subclass instances.
	 *
	 * @var array
	 */
	protected static array $instances = [];

	/**
	 * Receives static method calls and redirect them
	 * over to the underlying class instance.
	 *
	 * @param string $method
	 * @param array $arguments
	 * @return mixed
	 */
	public static function __callStatic( string $method, array $arguments ): mixed
	{
		return static::getInstance()->{ $method }( ...$arguments );
	}

	/**
	 * Returns underlying class' instance.
	 *
	 * @return object
	 */
	public static function getInstance(): object
	{
		$namespace = static::getFacadeAccessor();

		return static::$instances[ $namespace ] ??
			   static::$instances[ $namespace ] = new $namespace;
	}

	/**
	 * Makes sure throw runtime exception when the
	 * parent class is not override this method.
	 *
	 * @throws RuntimeException always
	 * @return string
	 */
	public static function getFacadeAccessor(): string
	{
		throw new RuntimeException( 'Facade should implements getFacadeAccessor method.' );
	}
}
