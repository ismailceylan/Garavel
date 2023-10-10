<?php

namespace Garavel\Http;

class ServerVariables
{
	/**
	 * Server variables.
	 */
	public array $data = [];

	/**
	 * Construct server variables.
	 */
	public function __construct( array $data = null )
	{
		$this->data = $data ?? $_SERVER;
	}

	/**
	 * Returns a server variable.
	 */
	public function get( string $key ): mixed
	{
		return array_key_exists( $key, $this->data )
			? $this->data[ $key ]
			: null;
	}
}
