<?php

spl_autoload_register( function( $namespace )
{
	if( strpos( $namespace, 'garavel' ) === -1 )
	{
		return;
	}
	
	$dir = implode(
		separator: DIRECTORY_SEPARATOR,
		array: array_slice(
			array: explode( '\\', __DIR__ ),
			offset: 0,
			length: -1
		)
	);
	
	$fullPath = $dir . DIRECTORY_SEPARATOR . $namespace . '.php';

	if( file_exists( $fullPath ))
	{
		require_once $fullPath;
	}
});
