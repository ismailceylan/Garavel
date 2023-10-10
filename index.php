<?php

use App\Http\Kernel;
use Garavel\Support\Facades\Request;
use Garavel\Support\Facades\Route;

define( 'GaravelStart', microtime( true ));

require_once './Garavel/autoload.php';
require_once './Garavel/Support/helpers.php';

Route::group(
[
	'middleware' => Kernel::$groupedMiddlewares[ 'web' ]
], function()
{
	require_once './routes/web.php';
});

Route::group(
[
	'prefix' => 'api/',
	'middleware' => Kernel::$groupedMiddlewares[ 'api' ]
],
function()
{
	require_once './routes/api.php';
});

Route::match(
	Request::getInstance()
);
