<?php

namespace Garavel\Routing;

class RouteGroupCollection
{
	/**
	 * Groups stack.
	 */
	public array $groups = [];

	/**
	 * Pushes and adds a group into stack.
	 */
	public function push( array $options )
	{
		$this->groups[] = new RouteGroup( $options );
	}

	/**
	 * Removes latest group from the stack and return that group.
	 */
	public function pop(): RouteGroup
	{
		return array_pop( $this->groups );
	}

	/**
	 * Returns latest group from the stack.
	 */
	public function latest(): null|RouteGroup
	{
		return $this->groups[ count( $this->groups ) - 1 ] ?? null;
	}

	/**
	 * Merges all the group prefixes and returns it.
	 */
	public function prefix( string $rest = '' ): string
	{
		$prefix = $rest;

		foreach( array_reverse( $this->groups ) as $group )
		{
			$prefix = $group->prefix( rest: $prefix );
		}

		return $prefix;
	}

	/**
	 * Merges all the namespace prefixes and returns it.
	 */
	public function namespace( string $rest = '' ): string
	{
		$ns = $rest;

		foreach( array_reverse( $this->groups ) as $group )
		{
			$ns = $group->namespace( rest: $ns );
		}

		return $ns;
	}

	/**
	 * Merges all the group wheres and returns it.
	 */
	public function wheres( array $stack = []): array
	{
		foreach( array_reverse( $this->groups ) as $group )
		{
			$stack = array_merge( $stack, ( array ) $group->where );
		}

		return $stack;
	}
	
	/**
	 * Merges all the group middlewares and returns it.
	 */
	public function middlewares( array $stack = []): array
	{
		foreach( $this->groups as $group )
		{
			$stack = array_merge( $stack, ( array ) $group->middleware );
		}

		return $stack;
	}
}
