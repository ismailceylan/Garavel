<?php

use Garavel\Support\Facades\Request;
use Garavel\Support\Facades\Response;
use Garavel\Support\Facades\JsonResponse;

function response()
{
	return Response::getInstance();
}

function jsonResponse()
{
	return JsonResponse::getInstance();
}

function request()
{
	return Request::getInstance();
}

function dd( ...$args )
{
	var_dump( ...$args );
	exit;
}

function dump( ...$args )
{
	var_dump( ...$args );
}
