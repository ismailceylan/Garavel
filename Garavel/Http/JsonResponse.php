<?php

namespace Garavel\Http;

class JsonResponse extends Response
{
	/**
	 * Json data source.
	 */
	public array $data = [];

	/**
	 * Construct a json response.
	 */
	public function __construct( array $data = null )
	{
		parent::__construct();
		
		$this->header( 'Content-Type', 'application/json' );

		if( $data )
		{
			$this->write( $data );
		}
	}

	/**
	 * Extends the data array with given array or object.
	 */
	public function write( $data ): JsonResponse
	{
		if( is_array( $data ) || is_object( $data ))
		{
			$this->data = array_merge((array) $this->data, (array) $data );
		}
		else if( is_string( $data ) || is_int( $data ) || is_bool( $data ))
		{
			$this->data[] = $data;
		}

		return $this;
	}

	/**
	 * Flushes the json response.
	 */
	public function flush(): void
	{
		parent::write( json_encode( $this->data ));
		parent::flush();
	}

	/**
	 * Responses a successfull json.
	 */
	public function success(
		string $message = 'Successful.',
		int $status = 200,
		array $extend = []
	): JsonResponse
	{
		return $this->set( 'success', $message, $status, $extend );
	}

	/**
	 * Responses a failed json.
	 */
	public function fail(
		string $message = 'Failed.',
		int $status = 500,
		array $extend = []
	): JsonResponse
	{
		return $this->set( 'failed', $message, $status, $extend );
	}

	/**
	 * Responses not found error.
	 */
	public function notFound(
		string $message = 'Not found.',
		array $extend = []
	): JsonResponse
	{
		return $this->set( 'not-found', $message, 404, $extend );
	}

	/**
	 * Sets a packed response's options at once.
	 */
	public function set(
		string $statusName,
		string $msg,
		int $statusCode,
		array $props
	): JsonResponse
	{
		$this->status = $statusCode;

		$this->data = array_merge(
			[
				'status' => $statusName,
				'message' => $msg
			],
			$props
		);

		return $this;
	}

}
