<?php

namespace Garavel\Routing;

use Garavel\Support\Str;

class RouteGroup
{
	public function __construct(
		public array $options
	){}

	/**
	 * Proxies the options prop.
	 */
	public function __get( string $key ): mixed
	{
		return $this->options[ $key ] ?? null;
	}

	/**
	 * Returns prefix options of the group.
	 */
	public function prefix( string $rest, string $glue = '/' ): string
	{
		return Str::mergeWith( $glue, $this->prefix ?? '', $rest );
	}

	/**
	 * Returns namespace options of the group.
	 */
	public function namespace( string $rest, string $glue = '\\' ): string
	{
		return Str::mergeWith( $glue, $this->namespace ?? '', $rest );
	}
}
