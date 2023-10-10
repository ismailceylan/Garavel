<?php

namespace Garavel\Routing;

class RouteExpression
{
	/**
	 * The route expression compiled to regexp.
	 */
	public string $pattern = '//';

	/**
	 * Expression segments.
	 */
	public array $segments = [];

	/**
	 * Constructor.
	 */
	public function __construct(
		/**
		 * The owner route. 
		 */
		public Route $route,

		/**
		 * Expression.
		 */
		public string $expression
	){}

	/**
	 * Compiles route expression to regular expression.
	 *
	 * @return string
	 */
	public function pattern(): string
	{
		$segmentPattern = '/\\\{(\??)(\w+)\\\}/';
		$eatEverything = '\w+';
		$optional = '?';
		$required = '';

		$segmentHandler = function( $match ) use ( $eatEverything, $optional, $required )
		{
			list(, $requiredFlagFromRouteExpr, $segmentName ) = $match;

			list( $pattern, $isRequired ) = 
				$this->route->wheres[ $segmentName ]
				??
				[ $eatEverything, $requiredFlagFromRouteExpr === $required ];

			$isRequired = $isRequired === null
				? $requiredFlagFromRouteExpr === $required
				: $isRequired;

			$this->segments[] = $segmentName;

			return "(?P<{$segmentName}>$pattern)" . ( $isRequired? $required : $optional );
		};

		return preg_replace_callback(
			$segmentPattern,
			$segmentHandler,
			preg_quote( $this->expression )
		);
	}
	
}
