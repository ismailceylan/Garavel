<?php

namespace Garavel\Http;

use Garavel\Support\Arr;
use Garavel\Support\Str;

class Response
{
	/**
	 * Http status code.
	 */
	public int $status = 200;

	/**
	 * Header stack.
	 */
	public array $headers = [];

	/**
	 * Response body.
	 */
	public array $body = [];

	/**
	 * Construct responses.
	 */
	public function __construct()
	{
		$this->header( 'Access-Control-Allow-Origin', '*' );

		$this->header(
			'Access-Control-Allow-Headers',
			'Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With'
		);
	}

	/**
	 * Adds multiple headers.
	 */
	public function withHeaders( array $headers ): Response
	{
		foreach( $headers as $key => $val )
		{
			$this->header( $key, $val );
		}

		return $this;
	}

	/**
	 * Sets a header.
	 */
	public function header( string $key, mixed $value = null ): Response
	{
		$this->headers[ $key ] = $value;
		return $this;
	}

	/**
	 * Returns true if given header name exists.
	 */
	public function hasHeader( string $key ): bool
	{
		return array_key_exists( $key, $this->headers );
	}

	/**
	 * Returns a header's value.
	 */
	public function getHeader( string $key ): mixed
	{
		return $this->headers[ $key ] ?? null;
	}

	/**
	 * Writes to body.
	 */
	public function write( $str ): Response
	{
		$contentLength = 'Content-Length';
		
		$this->header( $contentLength,
			( $this->getHeader( $contentLength ) ?? 0 ) + mb_strlen( $str )
		);

		$this->body[] = $str;

		return $this;
	}

	/**
	 * Flushes all the headers and the body.
	 */
	public function flush(): void
	{
		$this->header( "HTTP/1.1 $this->status" );

		foreach( $this->headers as $key => $val )
		{
			header( $key . Str::prefix( ': ', $val ));
		}

		echo Arr::join( $this->body, '' );
	}

	/**
	 * Sets http status code.
	 */
	public function status( int $status ): Response
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * Returns an object that generates json responses.
	 */
	public function json( mixed $data = null, int $status = 200 ): JsonResponse
	{
		return new JsonResponse( $data, $status );
	}

}
