<?php

namespace Garavel\Routing\Exceptions;

use Exception;

class UnknownControllerMethod extends Exception
{
	public function __construct( string $namespace, string $methodName )
	{
		parent::__construct(
			"$methodName method doesn't exists in $namespace controller."
		);
	}
}
