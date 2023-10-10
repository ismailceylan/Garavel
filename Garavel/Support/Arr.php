<?php

namespace Garavel\Support;

class Arr
{
	/**
	 * Returns latest element of an array.
	 */
	public static function latest( array $arr ): mixed
	{
		return $arr[ count( $arr ) - 1 ];
	}

	/**
	 * Join array elements with a string.
	 */
	public static function join( array $arr, string $glue = ', ' ): string
	{
		return implode( $glue, $arr );
	}

	/**
	 * Iterates over each value in the array passing
	 * them to the callback function.
	 */
	public static function filter( array $arr, callable $callback, ?int $mode = 0 ): array
	{
		return array_filter( $arr, $callback, $mode );
	}

	/**
	 * Collects not empty items in a new array and returns it.
	 */
	public static function clean( array $arr ): array
	{
		return static::filter( $arr, fn( $item ) => ! empty( $item ));
	}

	/**
	 * Plucks object property values into an array.
	 */
	public static function pluck( array $arr, string $key ): array
	{
		$stack = [];

		foreach( $arr as $item )
		{
			$stack[] = $item->{ $key };
		}

		return $stack;
	}

	/**
	 * Flattens a multidimensional array.
	 */
	public static function flat( array $arr ): array
	{
		$tmp = [];

		foreach( $arr as $item )
		{
			if( is_array( $item ))
			{
				$tmp = [ ...$tmp, ...self::flat( $item )];
			}
			else
			{
				$tmp[] = $item;
			}
		}

		return $tmp;
	}

	/**
	 * Tries to parse string types to their possible
	 * builtin types.
	 */
	public static function normalize( array $arr ): array
	{
		foreach( $arr as &$item )
		{
			if( is_numeric( $item ))
			{
				$item = (float) $item;
			}
			else if( is_array( $item ))
			{
				$item = self::normalize( $item );
			}
			else if( Str::isBoolable( $item ))
			{
				$item = Str::parseBool( $item );
			}
			else if( Str::isArrayable( $item ))
			{
				$item = self::normalize(
					Str::split( $item, Str::splitter( $item ))
				);
			}
		}

		return $arr;
	}
}
