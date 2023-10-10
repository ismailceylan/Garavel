<?php

namespace Garavel\Support;

class Str
{
	/**
	 * Merges two string with a glue. It makes
	 * sure that the glue doesn't repeat.
	 */
	public static function mergeWith( string $glue, string $left, string $right  ): string
	{
		return Arr::join(
			Arr::clean(
			[
				...static::split( $left, $glue ),
				...static::split( $right, $glue )
			]),
			$glue
		);
	}

	/**
	 * Makes the given string begin with the given string. If
	 * it is, it doesn't touch anything.
	 */
	public static function startWith( string $str, string $start, bool $insensitive = true ): string
	{
		return self::startsWith( $str, $start, $insensitive )
			? $start . Str::slice( $str, Str::len( $start ))
			: $start . $str;
	}

	/**
	 * Indicates whether a given string begins with a given string.
	 */
	public static function startsWith( string $str, string $starts, bool $insensitive = true ): bool
	{
		$strStart = Str::slice( $str, 0, Str::len( $starts ));

		if( $insensitive )
		{
			$strStart = strtolower( $strStart );
			$starts = strtolower( $starts );
		}

		return $strStart == $starts;
	}

	/**
	 * Split a string by a string.
	 */
	public static function split( string $str, string $separator ): array
	{
		return explode( $separator, $str );
	}

	/**
	 * Prefixes given string with the given prefix. If
	 * the string is empty then empty string will return.
	 */
	public static function prefix( string $prefix, string|null $str ): string
	{
		return empty( $str )? '' : $prefix . $str;
	}

	/**
	 * Returns length of a string.
	 */
	public static function len( string $str, bool $multibyte = false ): int
	{
		return $multibyte? mb_strlen( $str ) : strlen( $str );
	}

	/**
	 * Slices a string.
	 */
	public static function slice( string $str, int $start, int $length = null, bool $multibyte = false ): string
	{
		return $multibyte
			? mb_substr( $str, $start, $length )
			: substr( $str, $start, $length );
	}

	/**
	 * Returns true if the needle exists in the string.
	 */
	public static function contains( string $str, string $needle, int $offset = null ): bool
	{
		return strpos( $str, $needle, $offset );
	}

	/**
	 * Converts string true or string false values
	 * to their boolean equivalent.
	 */
	public static function parseBool( string|int|bool $str ): bool
	{
		if( $str === 'false' || $str === false || $str === '0' || $str === 0 )
		{
			return false;
		}
		
		return true;
	}

	/**
	 * Checks if the given string convertable to boolean
	 * equivalent.
	 */
	public static function isBoolable( mixed $str ): bool
	{
		return $str === 'true' || $str === 'false' ||
			   $str ===  true  || $str ===  false;
	}

	/**
	 * Checks if the given string can split by a
	 * comma "," or pipe "|" character to an array.
	 */
	public static function isArrayable( string $str ): bool
	{
		return self::splitter( $str ) !== false;
	}

	/**
	 * Returns comma "," if the given string has it or
	 * returns a pipe "|" if string has it.
	 */
	public static function splitter( string $str ): string|false
	{
		if( strpos( $str, ',' ) !== false )
		{
			return ',';
		}
		else if( strpos( $str, '|' ))
		{
			return '|';
		}

		return false;
	}
}
