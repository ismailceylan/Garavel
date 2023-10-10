<?php

namespace Garavel\Routing;

class Matches
{
	/**
	 * Named matches.
	 */
	public array $matches = [];

	/**
	 * Ordinary matches.
	 */
	public array $values = [];

	/**
	 * Indicates whether there is a match.
	 */
	public bool $hasMatched = false;

	/**
	 * Constructor.
	 */
	public function __construct(
		public string $haystack,
		public RouteExpression $expression
	)
	{
		$this->fullfillSegments(
			$this->search()
		);
	}

	/**
	 * Returns segment value.
	 */
	public function __get( string $key ): string
	{
		return $this->matches[ $key ] ?? null;
	}

	/**
	 * Sets segment value.
	 */
	public function __set( string $key, mixed $val )
	{
		$this->matches[ $key ] = $val;
	}

	/**
	 * Checks if the given key exists.
	 */
	public function __isset( string $key ): bool
	{
		return array_key_exists( $key, $this->matches );
	}

	/**
	 * Executes the expression in the haystack and
	 * returns the result as an array.
	 */
	private function search(): array
	{
		$flags = PREG_OFFSET_CAPTURE;
		$haystack = $this->haystack;
		$pattern = '#^' . $this->expression->pattern() . '$#u';

		$this->hasMatched = preg_match( $pattern, $haystack, $matches, $flags ) === 1;

		return $matches;
	}

	/**
	 * Fullfills segments.
	 */
	private function fullfillSegments( array $matches ): void
	{
		foreach( $this->expression->segments as $segmentName )
		{
			$this->matches[ $segmentName ] =
				$matches[ $segmentName ][ 0 ] ?? null;
		}

		$this->values = array_values( $this->matches );
	}
}
