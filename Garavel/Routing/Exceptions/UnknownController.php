<?php

namespace Garavel\Routing\Exceptions;

use Exception;

class UnknownController extends Exception
{
	public function __construct( public string $controllerNS )
	{
		parent::__construct( "Controller $controllerNS doesn't exist." );
	}
}
