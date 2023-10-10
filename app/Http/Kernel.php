<?php

namespace App\Http;

class Kernel
{
    /**
	 * Common middlewares.
	 * These are gonna run during every request.
     */
    public static array $commonMiddlewares =
	[

	];

	/**
	 * Categorized middlewares.
	 * They are gonna work if they match the type of the request.
	 */
	public static array $groupedMiddlewares = 
	[
		'api' =>
		[
			
		],

		'web' =>
		[

		]
	];

	/**
	 * They are middleware shortcuts.
	 * 
	 * It allows to call middleware classes by their short names.
	 */
	public static array $middlewareAliases =
	[

	];
}
