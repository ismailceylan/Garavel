<?php

namespace Garavel\Http;

class Request
{
	/**
	 * POST, GET and Input datas.
	 */
	public array $data;

	/**
	 * Server variables.
	 */
	public ServerVariables $server;

	/**
	 * Request constructor.
	 */
	public function __construct()
	{
		$_INPUT = json_decode(
			file_get_contents( 'php://input' )
		);

		$this->data = array_merge( $_POST, $_GET, (array) $_INPUT );
		$this->server = new ServerVariables;
	}

	/**
	 * Returns the value for the given key.
	 */
	public function __get( string $key ): mixed
	{
		return $this->input( $key );
	}

	/**
	 * Returns the variable value from any source. Returns the
	 * default value if the variable does not exist.
	 */
	public function input( string $key = null, mixed $default = null ): mixed
	{
		return $key === null
			? $this->data
			: $this->data[ $key ] ?? $default;
	}

	/**
	 * Returns the requested directory name without segments
	 * such as root directory and application name.
	 */
	public function path(): string
	{
		return str_replace(
			pathinfo( $this->server->get( 'SCRIPT_NAME' ))[ 'dirname' ] . '/',
			'',
			$this->server->get( 'REDIRECT_URL' ) ?? ''
		);
	}

	/**
	 * Returns the request method in all uppercase.
	 */
	public function method(): string
	{
		return strtoupper(
			$this->server->get( 'REQUEST_METHOD' )
		);
	}

	/**
	 * Returns true if the "X-Requested-With" header is
	 * equal to "XmlHttpRequest". 
	 */
	public function ajax(): bool
	{
		return $this->server->get( 'HTTP_X_REQUESTED_WITH' ) == 'XmlHttpRequest';
	}
}
